<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Plugin\Quote;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Pinblooms\CheckoutGuard\Model\Config;
use Pinblooms\CheckoutGuard\Model\EmailValidator;
use Pinblooms\CheckoutGuard\Model\GuestCheckmoGuard;
use Pinblooms\CheckoutGuard\Model\GuestCheckoutGuard;
use Pinblooms\CheckoutGuard\Model\GuestQuoteChecker;
use Pinblooms\CheckoutGuard\Model\GuardLogger;
use Pinblooms\CheckoutGuard\Model\QuoteSanityValidator;

/**
 * Final safety net for every place-order path (GraphQL, REST, and others).
 * Blocks template-injection addresses and guest Check/Money Order.
 */
class ValidateBeforePlaceOrder
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly Config $config,
        private readonly EmailValidator $emailValidator,
        private readonly GuestCheckmoGuard $guestCheckmoGuard,
        private readonly GuestCheckoutGuard $guestCheckoutGuard,
        private readonly GuestQuoteChecker $guestQuoteChecker,
        private readonly GuardLogger $guardLogger,
        private readonly QuoteSanityValidator $quoteSanityValidator
    ) {
    }

    /**
     * @param int|string $cartId
     * @param mixed $paymentMethod
     * @return null
     * @throws LocalizedException
     */
    public function beforePlaceOrder(
        QuoteManagement $subject,
        $cartId,
        $paymentMethod = null
    ) {
        if (!$this->config->isEnabled()) {
            return null;
        }

        try {
            /** @var Quote $quote */
            $quote = $this->cartRepository->getActive((int) $cartId);
        } catch (\Throwable $exception) {
            return null;
        }

        $storeId = $quote->getStoreId() ? (int) $quote->getStoreId() : null;
        $isGuest = $this->guestQuoteChecker->isGuestQuote($quote);

        try {
            // Always block Magento template markers in addresses (guest or logged-in).
            if ($this->config->isAddressInjectionBlockingEnabled($storeId)) {
                $this->quoteSanityValidator->assertGuestQuoteCanPlaceOrder($quote);
            } elseif ($isGuest) {
                $this->quoteSanityValidator->assertGuestQuoteCanPlaceOrder($quote);
            }

            if ($isGuest) {
                $this->guestCheckoutGuard->assertGuestCheckoutAllowed($storeId);

                if ($this->config->isGuestEmailBlockingEnabled($storeId)) {
                    $this->emailValidator->assertAllowedGuestEmail(
                        $quote->getCustomerEmail(),
                        $storeId
                    );
                }

                $this->guestCheckmoGuard->assertGuestMayPlaceOrderWithCheckmo($quote, $storeId);
            }
        } catch (LocalizedException | GraphQlInputException $exception) {
            $this->guardLogger->logBlocked(
                'place_order_core',
                (string) $exception->getMessage(),
                $storeId,
                [
                    'quote_id' => (string) $quote->getId(),
                    'is_guest' => $isGuest ? '1' : '0',
                    'payment' => (string) $quote->getPayment()->getMethod(),
                ]
            );
            throw new LocalizedException(__($exception->getMessage()));
        }

        return null;
    }
}
