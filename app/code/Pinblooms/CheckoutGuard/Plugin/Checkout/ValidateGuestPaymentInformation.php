<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Plugin\Checkout;

use Magento\Checkout\Model\GuestPaymentInformationManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Pinblooms\CheckoutGuard\Model\AddressValidator;
use Pinblooms\CheckoutGuard\Model\Config;
use Pinblooms\CheckoutGuard\Model\EmailValidator;
use Pinblooms\CheckoutGuard\Model\GuestCheckmoGuard;
use Pinblooms\CheckoutGuard\Model\GuestCheckoutGuard;
use Pinblooms\CheckoutGuard\Model\GuestQuoteChecker;
use Pinblooms\CheckoutGuard\Model\GuardLogger;
use Pinblooms\CheckoutGuard\Model\QuoteSanityValidator;
use Pinblooms\CheckoutGuard\Model\RateLimiter;

/**
 * Guard REST guest checkout (V1/guest-carts/.../payment-information).
 * GraphQL plugins alone do not cover this path.
 */
class ValidateGuestPaymentInformation
{
    public function __construct(
        private readonly AddressValidator $addressValidator,
        private readonly Config $config,
        private readonly EmailValidator $emailValidator,
        private readonly GuestCheckmoGuard $guestCheckmoGuard,
        private readonly GuestCheckoutGuard $guestCheckoutGuard,
        private readonly GuestQuoteChecker $guestQuoteChecker,
        private readonly GuardLogger $guardLogger,
        private readonly QuoteSanityValidator $quoteSanityValidator,
        private readonly RateLimiter $rateLimiter
    ) {
    }

    /**
     * @param string $cartId
     * @param string $email
     * @return null
     * @throws LocalizedException
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagement $subject,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ) {
        $this->assertAllowed((string) $cartId, (string) $email, $paymentMethod, $billingAddress);
        return null;
    }

    /**
     * @param string $cartId
     * @param string $email
     * @return null
     * @throws LocalizedException
     */
    public function beforeSavePaymentInformation(
        GuestPaymentInformationManagement $subject,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ) {
        $this->assertAllowed((string) $cartId, (string) $email, $paymentMethod, $billingAddress);
        return null;
    }

    /**
     * @throws LocalizedException
     */
    private function assertAllowed(
        string $maskedCartId,
        string $email,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress
    ): void {
        if (!$this->config->isEnabled()) {
            return;
        }

        try {
            $this->rateLimiter->assertAllowed('rest_guest_payment', $maskedCartId);
        } catch (LocalizedException $exception) {
            throw $exception;
        }

        try {
            $quote = $this->guestQuoteChecker->getQuoteByMaskedCartId($maskedCartId);
        } catch (\Throwable $exception) {
            return;
        }

        $storeId = $quote->getStoreId() ? (int) $quote->getStoreId() : null;

        try {
            $this->guestCheckoutGuard->assertGuestCheckoutAllowed($storeId);

            if ($this->config->isGuestEmailBlockingEnabled($storeId)) {
                $this->emailValidator->assertAllowedGuestEmail(
                    $email !== '' ? $email : $quote->getCustomerEmail(),
                    $storeId
                );
            }

            if ($this->config->isAddressInjectionBlockingEnabled($storeId) && $billingAddress) {
                $this->addressValidator->assertAllowedAddress(
                    $this->addressInterfaceToArray($billingAddress),
                    $storeId
                );
            }

            $methodCode = (string) $paymentMethod->getMethod();
            if ($methodCode === GuestCheckmoGuard::CHECKMO_CODE) {
                $this->guestCheckmoGuard->assertGuestMaySelectCheckmo($quote, $storeId);
            }

            $this->quoteSanityValidator->assertGuestQuoteCanPlaceOrder($quote);
        } catch (LocalizedException | GraphQlInputException $exception) {
            $this->guardLogger->logBlocked(
                'rest_guest_payment',
                (string) $exception->getMessage(),
                $storeId,
                [
                    'cart_id' => $maskedCartId,
                    'email' => $email,
                    'payment' => (string) $paymentMethod->getMethod(),
                ]
            );
            throw new LocalizedException(__($exception->getMessage()));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function addressInterfaceToArray(AddressInterface $address): array
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
