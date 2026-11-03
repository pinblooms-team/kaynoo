<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Model;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * Collects request metadata for security logs only.
 * Does not alter checkout behavior or Payment Failed emails.
 */
class RequestMetadata
{
    private const MAX_UA_LENGTH = 255;

    public function __construct(
        private readonly RemoteAddress $remoteAddress,
        private readonly RequestInterface $request
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getLogContext(?int $storeId = null): array
    {
        $context = [];

        $clientIp = $this->resolveClientIp();
        if ($clientIp !== '') {
            $context['client_ip'] = $clientIp;
        }

        $forwarded = $this->getHeader('X-Forwarded-For');
        if ($forwarded !== '') {
            // Keep raw header for proxy debugging (truncate if huge chain).
            $context['forwarded_for'] = mb_substr($forwarded, 0, 200);
        }

        $userAgent = $this->getHeader('User-Agent');
        if ($userAgent !== '') {
            $context['user_agent'] = mb_substr($userAgent, 0, self::MAX_UA_LENGTH);
        }

        $country = $this->resolveCountry();
        if ($country !== '') {
            $context['geo_country'] = $country;
        }

        $city = $this->resolveCity();
        if ($city !== '') {
            $context['geo_city'] = $city;
        }

        $region = $this->getHeader('CF-Region')
            ?: $this->getHeader('CF-Region-Code');
        if ($region !== '') {
            $context['geo_region'] = mb_substr($region, 0, 80);
        }

        return $context;
    }

    public function getClientIp(): string
    {
        return $this->resolveClientIp();
    }

    private function resolveClientIp(): string
    {
        // Prefer first public IP in X-Forwarded-For (Cloudflare / load balancer).
        $forwarded = $this->getHeader('X-Forwarded-For');
        if ($forwarded !== '') {
            $parts = array_map('trim', explode(',', $forwarded));
            foreach ($parts as $part) {
                if ($part !== '' && filter_var($part, FILTER_VALIDATE_IP)) {
                    return $part;
                }
            }
        }

        $realIp = $this->getHeader('X-Real-IP');
        if ($realIp !== '' && filter_var($realIp, FILTER_VALIDATE_IP)) {
            return $realIp;
        }

        $cfConnecting = $this->getHeader('CF-Connecting-IP');
        if ($cfConnecting !== '' && filter_var($cfConnecting, FILTER_VALIDATE_IP)) {
            return $cfConnecting;
        }

        try {
            $ip = (string) $this->remoteAddress->getRemoteAddress();
            return $ip !== '' && $ip !== 'false' ? $ip : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function resolveCountry(): string
    {
        $candidates = [
            $this->getHeader('CF-IPCountry'),
            $this->getHeader('CloudFront-Viewer-Country'),
            $this->getHeader('X-Country-Code'),
            $this->getHeader('X-Geo-Country'),
        ];

        foreach ($candidates as $code) {
            $code = strtoupper(trim($code));
            if ($code !== '' && $code !== 'XX' && preg_match('/^[A-Z]{2}$/', $code)) {
                return $code;
            }
        }

        return '';
    }

    private function resolveCity(): string
    {
        $candidates = [
            $this->getHeader('CF-IPCity'),
            $this->getHeader('X-Geo-City'),
        ];

        foreach ($candidates as $city) {
            $city = trim($city);
            if ($city !== '') {
                return mb_substr($city, 0, 80);
            }
        }

        return '';
    }

    private function getHeader(string $name): string
    {
        try {
            if ($this->request instanceof HttpRequest) {
                $value = $this->request->getHeader($name);
                if (is_string($value)) {
                    return trim($value);
                }
                if (is_array($value) && isset($value[0]) && is_string($value[0])) {
                    return trim($value[0]);
                }
            }

            // Fallback for non-HTTP request mocks.
            $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
            if (method_exists($this->request, 'getServer')) {
                $server = $this->request->getServer($serverKey);
                return is_string($server) ? trim($server) : '';
            }

            return '';
        } catch (\Throwable $e) {
            return '';
        }
    }
}
