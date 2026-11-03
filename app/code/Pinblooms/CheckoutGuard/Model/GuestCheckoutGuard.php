<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class GuestCheckoutGuard
{
    public const GUEST_CHECKOUT_DISABLED_MESSAGE =
        'Guest checkout is not available. Please sign in to continue.';

    public function __construct(
        private readonly Config $config,
        private readonly GuardLogger $guardLogger
    ) {
    }

    /**
     * @throws GraphQlInputException
     */
    public function assertGuestCheckoutAllowed(?int $storeId = null): void
    {
        if (!$this->config->isGuestCheckoutEnforcementEnabled($storeId)) {
            return;
        }

        if ($this->config->isMagentoGuestCheckoutAllowed($storeId)) {
            return;
        }

        $this->guardLogger->logBlocked(
            'guest_checkout_disabled',
            self::GUEST_CHECKOUT_DISABLED_MESSAGE,
            $storeId
        );

        throw new GraphQlInputException(__(self::GUEST_CHECKOUT_DISABLED_MESSAGE));
    }
}
