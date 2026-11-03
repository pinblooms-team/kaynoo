<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Model;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Detect bot / junk quotes so Payment Failed admin emails can be skipped.
 * Real customer declines (normal address + positive total) are not skipped.
 */
class PaymentFailedEmailGuard
{
    private const TEMPLATE_INJECTION_PATTERN =
        '/\{\{|\}\}|%\}|%\{|\$\{|<script|javascript:|\{\{depend|\{\{var|\{\{block|\{\{\/|Magento\\\\Email/i';

    public function __construct(
        private readonly Config $config,
        private readonly EmailValidator $emailValidator
    ) {
    }

    /**
     * @return string|null Skip reason, or null when the email should still send.
     */
    public function getSkipReason(CartInterface $quote, ?int $storeId = null): ?string
    {
        if (!$this->config->isPaymentFailedBotEmailSuppressEnabled($storeId)) {
            return null;
        }

        $storeId = $storeId ?? (int) $quote->getStoreId();

        if ($this->config->isPaymentFailedSkipTemplateAddressEnabled($storeId)
            && $this->addressHasTemplateInjection($quote)
        ) {
            return 'billing/shipping contains Magento template markers';
        }

        if ($this->config->isPaymentFailedSkipZeroTotalEnabled($storeId)
            && (float) $quote->getGrandTotal() <= 0.00001
        ) {
            return 'grand total is zero';
        }

        if ($this->config->isPaymentFailedSkipEmptyGuestNameEnabled($storeId)
            && $this->isEmptyGuestName($quote)
        ) {
            return 'guest with empty name';
        }

        if ($this->config->isPaymentFailedSkipJunkGuestEmailEnabled($storeId)
            && $this->emailValidator->isBlockedGuestEmail((string) $quote->getCustomerEmail(), $storeId)
        ) {
            return 'guest email matches blocked bot/disposable pattern';
        }

        return null;
    }

    public function shouldSkip(CartInterface $quote, ?int $storeId = null): bool
    {
        return $this->getSkipReason($quote, $storeId) !== null;
    }

    /**
     * Full attempt payload for checkout_guard.log (what the bot tried to send).
     *
     * @return array<string, scalar|null>
     */
    public function buildAttemptLogContext(
        CartInterface $quote,
        string $skipReason,
        $paymentMessage = '',
        string $checkoutType = 'onepage'
    ): array {
        $billing = $quote->getBillingAddress();
        $shipping = $quote->getShippingAddress();
        $paymentMethod = '';
        try {
            if ($quote->getPayment()) {
                $paymentMethod = (string) $quote->getPayment()->getMethod();
            }
        } catch (\Throwable $e) {
            $paymentMethod = '';
        }

        $items = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $items[] = sprintf(
                '%s x %s',
                (string) $item->getSku(),
                (string) $item->getQty()
            );
            if (count($items) >= 20) {
                break;
            }
        }

        $message = is_string($paymentMessage) ? $paymentMessage : '';

        return [
            'action' => 'payment_failed_email_skipped',
            'skip_reason' => $skipReason,
            'checkout_type' => $checkoutType,
            'payment_message' => $this->clip($message, 500),
            'payment_method' => $paymentMethod,
            'quote_id' => $quote->getId(),
            'masked_quote_id' => method_exists($quote, 'getMaskedId')
                ? (string) $quote->getMaskedId()
                : null,
            'store_id' => (int) $quote->getStoreId(),
            'is_guest' => (int) $quote->getCustomerId() > 0 ? 0 : 1,
            'customer_id' => $quote->getCustomerId(),
            'customer_email' => $this->clip((string) $quote->getCustomerEmail(), 200),
            'customer_firstname' => $this->clip((string) $quote->getCustomerFirstname(), 120),
            'customer_lastname' => $this->clip((string) $quote->getCustomerLastname(), 120),
            'grand_total' => $quote->getGrandTotal(),
            'base_grand_total' => $quote->getBaseGrandTotal(),
            'currency' => (string) $quote->getQuoteCurrencyCode(),
            'items' => $this->clip(implode('; ', $items), 500),
            'items_count' => count($quote->getAllVisibleItems()),
            'billing_firstname' => $billing ? $this->clip((string) $billing->getFirstname(), 200) : '',
            'billing_lastname' => $billing ? $this->clip((string) $billing->getLastname(), 200) : '',
            'billing_street' => $billing ? $this->clip($this->streetToString($billing), 500) : '',
            'billing_city' => $billing ? $this->clip((string) $billing->getCity(), 200) : '',
            'billing_region' => $billing ? $this->clip((string) $billing->getRegion(), 200) : '',
            'billing_postcode' => $billing ? $this->clip((string) $billing->getPostcode(), 80) : '',
            'billing_country' => $billing ? $this->clip((string) $billing->getCountryId(), 20) : '',
            'billing_telephone' => $billing ? $this->clip((string) $billing->getTelephone(), 80) : '',
            'billing_company' => $billing ? $this->clip((string) $billing->getCompany(), 200) : '',
            'billing_dump' => $billing ? $this->clip(implode(' | ', $this->flattenAddress($billing)), 1000) : '',
            'shipping_firstname' => $shipping ? $this->clip((string) $shipping->getFirstname(), 200) : '',
            'shipping_lastname' => $shipping ? $this->clip((string) $shipping->getLastname(), 200) : '',
            'shipping_street' => $shipping ? $this->clip($this->streetToString($shipping), 500) : '',
            'shipping_city' => $shipping ? $this->clip((string) $shipping->getCity(), 200) : '',
            'shipping_region' => $shipping ? $this->clip((string) $shipping->getRegion(), 200) : '',
            'shipping_postcode' => $shipping ? $this->clip((string) $shipping->getPostcode(), 80) : '',
            'shipping_country' => $shipping ? $this->clip((string) $shipping->getCountryId(), 20) : '',
            'shipping_dump' => $shipping ? $this->clip(implode(' | ', $this->flattenAddress($shipping)), 1000) : '',
        ];
    }

    private function addressHasTemplateInjection(CartInterface $quote): bool
    {
        foreach ([$quote->getBillingAddress(), $quote->getShippingAddress()] as $address) {
            if (!$address instanceof AddressInterface) {
                continue;
            }
            foreach ($this->flattenAddress($address) as $value) {
                if ($value !== '' && preg_match(self::TEMPLATE_INJECTION_PATTERN, $value)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isEmptyGuestName(CartInterface $quote): bool
    {
        if ((int) $quote->getCustomerId() > 0) {
            return false;
        }

        $first = '';
        $last = '';
        $billing = $quote->getBillingAddress();
        if ($billing instanceof AddressInterface) {
            $first = trim((string) $billing->getFirstname());
            $last = trim((string) $billing->getLastname());
        }

        return $first === '' && $last === '';
    }

    private function streetToString(AddressInterface $address): string
    {
        $street = $address->getStreet();
        if (!is_array($street)) {
            return $street !== null ? (string) $street : '';
        }

        return implode(', ', array_map('strval', $street));
    }

    private function clip(string $value, int $max): string
    {
        $value = trim($value);
        if (mb_strlen($value) <= $max) {
            return $value;
        }

        return mb_substr($value, 0, $max) . '…';
    }

    /**
     * @return string[]
     */
    private function flattenAddress(AddressInterface $address): array
    {
        $street = $address->getStreet();
        if (!is_array($street)) {
            $street = $street !== null && $street !== '' ? [(string) $street] : [];
        }

        $values = array_merge(
            [
                (string) $address->getFirstname(),
                (string) $address->getLastname(),
                (string) $address->getMiddlename(),
                (string) $address->getPrefix(),
                (string) $address->getSuffix(),
                (string) $address->getCompany(),
                (string) $address->getCity(),
                (string) $address->getRegion(),
                (string) $address->getPostcode(),
                (string) $address->getTelephone(),
                (string) $address->getFax(),
                (string) $address->getVatId(),
                (string) $address->getCountryId(),
            ],
            array_map('strval', $street)
        );

        return array_values(array_filter($values, static fn(string $v): bool => trim($v) !== ''));
    }
}
