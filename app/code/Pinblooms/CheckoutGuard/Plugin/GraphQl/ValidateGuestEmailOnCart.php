<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Plugin\GraphQl;

use Pinblooms\CheckoutGuard\Model\Config;
use Pinblooms\CheckoutGuard\Model\EmailValidator;
use Pinblooms\CheckoutGuard\Model\GraphQlInputReader;
use Pinblooms\CheckoutGuard\Model\GuestCheckoutGuard;
use Pinblooms\CheckoutGuard\Model\GuardLogger;
use Pinblooms\CheckoutGuard\Model\RateLimiter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\SetGuestEmailOnCart;

class ValidateGuestEmailOnCart
{
    public function __construct(
        private readonly Config $config,
        private readonly EmailValidator $emailValidator,
        private readonly GraphQlInputReader $inputReader,
        private readonly GuestCheckoutGuard $guestCheckoutGuard,
        private readonly GuardLogger $guardLogger,
        private readonly RateLimiter $rateLimiter
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeResolve(
        SetGuestEmailOnCart $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): void {
        if (!$this->config->isEnabled()) {
            return;
        }

        $cartId = $this->inputReader->getMaskedCartId($args);

        try {
            $this->rateLimiter->assertAllowed('guest_email', $cartId);
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        $this->guestCheckoutGuard->assertGuestCheckoutAllowed();

        if (!$this->config->isGuestEmailBlockingEnabled()) {
            return;
        }

        $email = $this->inputReader->getGuestEmail($args);

        try {
            $this->emailValidator->assertAllowedGuestEmail($email);
        } catch (LocalizedException $exception) {
            $this->guardLogger->logBlocked(
                'guest_email',
                (string) $exception->getMessage(),
                null,
                ['email' => is_string($email) ? $email : '']
            );
            throw new GraphQlInputException(__($exception->getMessage()));
        }
    }
}
