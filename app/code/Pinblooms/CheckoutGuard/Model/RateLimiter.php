<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Gentle per-IP (and optional per-cart) rate limit for checkout GraphQL mutations.
 * Does not disable Magento Payment Failed emails.
 */
class RateLimiter
{
    private const CACHE_TAG = 'PINBLOOMS_CHECKOUT_GUARD_RL';

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Config $config,
        private readonly RequestMetadata $requestMetadata,
        private readonly GuardLogger $guardLogger
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function assertAllowed(
        string $action,
        ?string $cartId = null,
        ?int $storeId = null
    ): void {
        if (!$this->config->isEnabled($storeId)
            || !$this->config->isRateLimitEnabled($storeId)
        ) {
            return;
        }

        $ip = $this->requestMetadata->getClientIp();
        if ($ip === '') {
            // Fail open if IP cannot be resolved (avoid locking all traffic).
            return;
        }

        $limit = $this->config->getRateLimitMaxAttempts($storeId);
        $window = $this->config->getRateLimitWindowSeconds($storeId);

        if ($limit < 1 || $window < 1) {
            return;
        }

        $ipKey = $this->buildCacheKey('ip', $ip);
        $ipCount = $this->increment($ipKey, $window);

        if ($ipCount > $limit) {
            $this->guardLogger->logBlocked(
                'rate_limit',
                (string) __('Too many checkout attempts. Please wait a minute and try again.'),
                $storeId,
                [
                    'action' => $action,
                    'cart_id' => $cartId ?: '',
                    'attempts' => $ipCount,
                    'limit' => $limit,
                    'window_seconds' => $window,
                ]
            );
            throw new LocalizedException(
                __('Too many checkout attempts. Please wait a minute and try again.')
            );
        }

        $cartLimit = $this->config->getRateLimitMaxAttemptsPerCart($storeId);
        if ($cartId && $cartLimit > 0) {
            $cartKey = $this->buildCacheKey('cart', $cartId);
            $cartCount = $this->increment($cartKey, $window);
            if ($cartCount > $cartLimit) {
                $this->guardLogger->logBlocked(
                    'rate_limit',
                    (string) __('Too many checkout attempts for this cart. Please wait a minute and try again.'),
                    $storeId,
                    [
                        'action' => $action,
                        'cart_id' => $cartId,
                        'attempts' => $cartCount,
                        'limit' => $cartLimit,
                        'window_seconds' => $window,
                        'scope' => 'cart',
                    ]
                );
                throw new LocalizedException(
                    __('Too many checkout attempts for this cart. Please wait a minute and try again.')
                );
            }
        }
    }

    private function buildCacheKey(string $scope, string $identity): string
    {
        return 'pinblooms_cg_rl_' . $scope . '_' . hash('sha256', $identity);
    }

    private function increment(string $cacheKey, int $windowSeconds): int
    {
        try {
            $raw = $this->cache->load($cacheKey);
            $count = is_numeric($raw) ? (int) $raw : 0;
            $count++;
            $this->cache->save(
                (string) $count,
                $cacheKey,
                [self::CACHE_TAG],
                $windowSeconds
            );
            return $count;
        } catch (\Throwable $e) {
            // Fail open — never break checkout if cache is down.
            return 0;
        }
    }
}
