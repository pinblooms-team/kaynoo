<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Plugin\GraphQl;

use Pinblooms\CheckoutGuard\Model\Config;
use Pinblooms\CheckoutGuard\Model\EmailValidator;
use Pinblooms\CheckoutGuard\Model\GraphQlInputReader;
use Pinblooms\CheckoutGuard\Model\GuestCheckmoGuard;
use Pinblooms\CheckoutGuard\Model\GuestCheckoutGuard;
use Pinblooms\CheckoutGuard\Model\GuestQuoteChecker;
use Pinblooms\CheckoutGuard\Model\GuardLogger;
use Pinblooms\CheckoutGuard\Model\QuoteSanityValidator;
use Pinblooms\CheckoutGuard\Model\RateLimiter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder;

class ValidateEmailBeforePlaceOrder
{
    public function __construct(
        private readonly Config $config,
        private readonly EmailValidator $emailValidator,
        private readonly GraphQlInputReader $inputReader,
        private readonly GuestCheckmoGuard $guestCheckmoGuard,
        private readonly GuestCheckoutGuard $guestCheckoutGuard,
        private readonly GuestQuoteChecker $guestQuoteChecker,
        private readonly GuardLogger $guardLogger,
        private readonly QuoteSanityValidator $quoteSanityValidator,
        private readonly RateLimiter $rateLimiter
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeResolve(
        PlaceOrder $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): void {
        if (!$this->config->isEnabled()) {
            return;
        }

        $maskedCartId = $this->inputReader->getMaskedCartId($args);

        try {
            $this->rateLimiter->assertAllowed('place_order', $maskedCartId);
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        if (!$maskedCartId) {
            return;
        }

        try {
            $quote = $this->guestQuoteChecker->getQuoteByMaskedCartId($maskedCartId);
        } catch (NoSuchEntityException $exception) {
            return;
        }

        if (!$this->guestQuoteChecker->isGuestQuote($quote)) {
            return;
        }

        $storeId = $quote->getStoreId() ? (int) $quote->getStoreId() : null;

        try {
            $this->guestCheckoutGuard->assertGuestCheckoutAllowed($storeId);

            if ($this->config->isGuestEmailBlockingEnabled($storeId)) {
                $this->emailValidator->assertAllowedGuestEmail(
                    $quote->getCustomerEmail(),
                    $storeId
                );
            }

            $this->guestCheckmoGuard->assertGuestMayPlaceOrderWithCheckmo($quote, $storeId);
            $this->quoteSanityValidator->assertGuestQuoteCanPlaceOrder($quote);
        } catch (LocalizedException | GraphQlInputException $exception) {
            $this->guardLogger->logBlocked(
                'place_order',
                (string) $exception->getMessage(),
                $storeId,
                ['cart_id' => $maskedCartId]
            );

            if ($exception instanceof GraphQlInputException) {
                throw $exception;
            }

            throw new GraphQlInputException(__($exception->getMessage()));
        }
    }
}
