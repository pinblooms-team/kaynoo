<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Model;

use Psr\Log\LoggerInterface;

/**
 * Logs blocked checkout attempts and skipped bot Payment Failed emails.
 */
class GuardLogger
{
    public function __construct(
        private readonly Config $config,
        private readonly LoggerInterface $logger,
        private readonly RequestMetadata $requestMetadata
    ) {
    }

    /**
     * @param array<string, scalar|null> $context
     */
    public function logBlocked(string $rule, string $message, ?int $storeId = null, array $context = []): void
    {
        if (!$this->config->isLoggingEnabled($storeId)) {
            return;
        }

        try {
            $meta = $this->requestMetadata->getLogContext($storeId);

            if (!$this->config->isLogClientIpEnabled($storeId)) {
                unset($meta['client_ip'], $meta['forwarded_for']);
            }
            if (!$this->config->isLogUserAgentEnabled($storeId)) {
                unset($meta['user_agent']);
            }
            if (!$this->config->isLogGeoEnabled($storeId)) {
                unset($meta['geo_country'], $meta['geo_city'], $meta['geo_region']);
            }

            $context = array_merge($meta, $context);

            $this->logger->info(
                sprintf('[CheckoutGuard] %s: %s', $rule, $message),
                $context
            );
        } catch (\Throwable $e) {
            // Never break checkout if logging fails.
            try {
                $this->logger->info(
                    sprintf('[CheckoutGuard] %s: %s', $rule, $message),
                    $context
                );
            } catch (\Throwable $ignored) {
                // Intentionally empty.
            }
        }
    }
}
