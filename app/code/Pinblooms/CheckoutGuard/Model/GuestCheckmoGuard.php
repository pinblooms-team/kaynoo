<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Model\Quote;

class GuestCheckmoGuard
{
    public const CHECKMO_CODE = 'checkmo';

    public const GUEST_CHECKMO_MESSAGE =
        'Check/Money Order is available for signed-in customers only. '
        . 'Please sign in or pay by credit card.';

    public function __construct(
        private readonly Config $config,
        private readonly GuestQuoteChecker $guestQuoteChecker
    ) {
    }

    /**
     * @throws GraphQlInputException
     */
    public function assertGuestMaySelectCheckmo(Quote $quote, ?int $storeId = null): void
    {
        if (!$this->config->isGuestCheckmoRestricted($storeId)) {
            return;
        }

        if (!$this->guestQuoteChecker->isGuestQuote($quote)) {
            return;
        }

        throw new GraphQlInputException(__(self::GUEST_CHECKMO_MESSAGE));
    }

    /**
     * Safety net at placeOrder for carts that still have checkmo selected.
     *
     * @throws GraphQlInputException
     */
    public function assertGuestMayPlaceOrderWithCheckmo(Quote $quote, ?int $storeId = null): void
    {
        if (!$this->config->isGuestCheckmoRestricted($storeId)) {
            return;
        }

        if (!$this->guestQuoteChecker->isGuestQuote($quote)) {
            return;
        }

        $paymentMethod = (string) $quote->getPayment()->getMethod();

        if ($paymentMethod !== self::CHECKMO_CODE) {
            return;
        }

        throw new GraphQlInputException(__(self::GUEST_CHECKMO_MESSAGE));
    }
}
