<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class QuoteSanityValidator
{
    public function __construct(
        private readonly AddressValidator $addressValidator
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function assertGuestQuoteCanPlaceOrder(Quote $quote): void
    {
        if ((int) $quote->getItemsCount() <= 0) {
            throw new LocalizedException(
                __('Your cart is empty. Please add items before placing an order.')
            );
        }

        $paymentMethod = (string) $quote->getPayment()->getMethod();

        if ($paymentMethod !== 'free') {
            $grandTotal = (float) $quote->getGrandTotal();

            if ($grandTotal <= 0.0) {
                throw new LocalizedException(
                    __('Unable to place an order with an invalid cart total.')
                );
            }
        }

        $storeId = $quote->getStoreId() ? (int) $quote->getStoreId() : null;

        $billingAddress = $quote->getBillingAddress();
        if ($billingAddress) {
            $this->addressValidator->assertAllowedAddress(
                $this->quoteAddressToArray($billingAddress),
                $storeId
            );
        }

        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress && !$quote->isVirtual()) {
            $this->addressValidator->assertAllowedAddress(
                $this->quoteAddressToArray($shippingAddress),
                $storeId
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function quoteAddressToArray($address): array
    {
        return [
            'firstname' => (string) $address->getFirstname(),
            'lastname' => (string) $address->getLastname(),
            'company' => (string) $address->getCompany(),
            'street' => (array) $address->getStreet(),
            'city' => (string) $address->getCity(),
            'postcode' => (string) $address->getPostcode(),
            'telephone' => (string) $address->getTelephone(),
            'region' => (string) $address->getRegion(),
        ];
    }
}
