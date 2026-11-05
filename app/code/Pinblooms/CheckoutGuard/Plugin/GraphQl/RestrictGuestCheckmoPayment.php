<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Plugin\GraphQl;

use Pinblooms\CheckoutGuard\Model\Config;
use Pinblooms\CheckoutGuard\Model\GraphQlInputReader;
use Pinblooms\CheckoutGuard\Model\GuestCheckmoGuard;
use Pinblooms\CheckoutGuard\Model\GuestQuoteChecker;
use Pinblooms\CheckoutGuard\Model\GuardLogger;
use Pinblooms\CheckoutGuard\Model\RateLimiter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\SetPaymentMethodOnCart;

class RestrictGuestCheckmoPayment
{
    public function __construct(
        private readonly Config $config,
        private readonly GraphQlInputReader $inputReader,
        private readonly GuestCheckmoGuard $guestCheckmoGuard,
        private readonly GuestQuoteChecker $guestQuoteChecker,
        private readonly GuardLogger $guardLogger,
        private readonly RateLimiter $rateLimiter
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeResolve(
        SetPaymentMethodOnCart $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): void {
        if (!$this->config->isEnabled()) {
            return;
        }

        $maskedCartId = $this->inputReader->getMaskedCartId($args);

        try {
            $this->rateLimiter->assertAllowed('set_payment', $maskedCartId);
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        if (!$this->config->isGuestCheckmoRestricted()) {
            return;
        }

        $paymentCode = $this->inputReader->getPaymentMethodCode($args);

        if ($paymentCode !== GuestCheckmoGuard::CHECKMO_CODE) {
            return;
        }

        if (!$maskedCartId) {
            return;
        }

        try {
            $quote = $this->guestQuoteChecker->getQuoteByMaskedCartId($maskedCartId);
        } catch (NoSuchEntityException $exception) {
            return;
        }

        $storeId = $quote->getStoreId() ? (int) $quote->getStoreId() : null;

        try {
            $this->guestCheckmoGuard->assertGuestMaySelectCheckmo($quote, $storeId);
        } catch (GraphQlInputException $exception) {
            $this->guardLogger->logBlocked(
                'guest_checkmo',
                (string) $exception->getMessage(),
                $storeId,
                ['cart_id' => $maskedCartId]
            );
            throw $exception;
        }
    }
}
