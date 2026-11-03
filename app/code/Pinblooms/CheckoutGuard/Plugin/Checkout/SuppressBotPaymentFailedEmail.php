<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Plugin\Checkout;

use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Quote\Api\Data\CartInterface;
use Pinblooms\CheckoutGuard\Model\Config;
use Pinblooms\CheckoutGuard\Model\GuardLogger;
use Pinblooms\CheckoutGuard\Model\PaymentFailedEmailGuard;

/**
 * Skip Magento "Payment Transaction Failed" admin emails for clear bot junk quotes.
 * Real customer declines still send. Skipped attempts are fully logged to checkout_guard.log
 * (same channel/IP/UA/geo as other CheckoutGuard blocks), including what the bot tried.
 */
class SuppressBotPaymentFailedEmail
{
    public function __construct(
        private readonly Config $config,
        private readonly PaymentFailedEmailGuard $emailGuard,
        private readonly GuardLogger $guardLogger
    ) {
    }

    /**
     * @param callable $proceed
     * @return CheckoutHelper
     */
    public function aroundSendPaymentFailedEmail(
        CheckoutHelper $subject,
        callable $proceed,
        $checkout,
        $message,
        $checkoutType = 'onepage'
    ) {
        if ($checkout instanceof CartInterface && $this->config->isEnabled((int) $checkout->getStoreId())) {
            $storeId = (int) $checkout->getStoreId();
            $reason = $this->emailGuard->getSkipReason($checkout, $storeId);

            if ($reason !== null) {
                // Same checkout_guard.log pipeline as other rules (IP / UA / geo via GuardLogger).
                $this->guardLogger->logBlocked(
                    'payment_failed_email',
                    'Skipped Payment Failed email: ' . $reason,
                    $storeId,
                    $this->emailGuard->buildAttemptLogContext(
                        $checkout,
                        $reason,
                        $message,
                        (string) $checkoutType
                    )
                );

                return $subject;
            }
        }

        return $proceed($checkout, $message, $checkoutType);
    }
}
