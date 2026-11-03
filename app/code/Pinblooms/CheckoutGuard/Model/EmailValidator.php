<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Guest-only email validation for GraphQL checkout.
 */
class EmailValidator
{
    private const MAX_EMAIL_LENGTH = 254;

    private const EMAIL_PATTERN = '/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i';

    private const TEMPLATE_INJECTION_PATTERN = '/\{\{|\}\}|%\}|%\{|\$\{|<script|javascript:/i';

    /** @var string[] */
    private const BLOCKED_DOMAINS = [
        'example.com',
        'example.org',
        'example.net',
        'test.test',
        'mailinator.com',
        'guerrillamail.com',
        'guerrillamail.net',
        'guerrillamail.org',
        'tempmail.com',
        'temp-mail.org',
        'yopmail.com',
        'trashmail.com',
        '10minutemail.com',
        'fakeinbox.com',
        'getnada.com',
        'sharklasers.com',
        'dispostable.com',
        'maildrop.cc',
        'throwaway.email',
        'mailnesia.com',
        'mintemail.com',
        'spam4.me',
        'bccto.me',
        'emailondeck.com',
        'mytemp.email',
    ];

    /** @var string[] Domains commonly used in bot probes when extended rules are on. */
    private const EXTENDED_BLOCKED_DOMAINS = [
        'email.com',
        'test.com',
    ];

    public function __construct(
        private readonly Config $config
    ) {
    }

    /**
     * @throws LocalizedException
     */
    /**
     * Non-throwing check used by Payment Failed email filter (same rules as GraphQL guest email).
     */
    public function isBlockedGuestEmail(?string $email, ?int $storeId = null): bool
    {
        try {
            $this->assertAllowedGuestEmail($email, $storeId);

            return false;
        } catch (LocalizedException $e) {
            return true;
        }
    }

    /**
     * @throws LocalizedException
     */
    public function assertAllowedGuestEmail(?string $email, ?int $storeId = null): void
    {
        if ($email === null || trim($email) === '') {
            throw new LocalizedException(
                __('Please enter a valid email address.')
            );
        }

        $email = strtolower(trim($email));

        if (strlen($email) > self::MAX_EMAIL_LENGTH) {
            throw new LocalizedException(
                __('Please enter a valid email address.')
            );
        }

        if (preg_match(self::TEMPLATE_INJECTION_PATTERN, $email)) {
            throw new LocalizedException(
                __('Please enter a valid email address.')
            );
        }

        if (!preg_match(self::EMAIL_PATTERN, $email)) {
            throw new LocalizedException(
                __('Please enter a valid email address.')
            );
        }

        $atPosition = strrpos($email, '@');
        if ($atPosition === false) {
            throw new LocalizedException(
                __('Please enter a valid email address.')
            );
        }

        $localPart = substr($email, 0, $atPosition);
        $domain = substr($email, $atPosition + 1);

        if ($localPart === '' || $domain === '') {
            throw new LocalizedException(
                __('Please enter a valid email address.')
            );
        }

        if ($this->isDomainBlocked($domain, $storeId)) {
            throw new LocalizedException(
                __('Please use a real email address you can receive order updates at.')
            );
        }

        if ($this->endsWith($domain, '.test') || $this->endsWith($domain, '.invalid')) {
            throw new LocalizedException(
                __('Please use a real email address you can receive order updates at.')
            );
        }

        if ($this->config->isExtendedEmailPatternsEnabled($storeId)) {
            $this->assertExtendedPatterns($localPart, $domain);
        }

        if ($this->config->isGmailPlusBlockingEnabled($storeId)) {
            $this->assertGmailPlusAllowed($email);
        }
    }

    /**
     * @throws LocalizedException
     */
    private function assertExtendedPatterns(string $localPart, string $domain): void
    {
        if (strlen($localPart) === 1) {
            throw new LocalizedException(
                __('Please use a real email address you can receive order updates at.')
            );
        }

        if (preg_match('/^(test|noreply|bot|x|admin|user)$/i', $localPart)) {
            throw new LocalizedException(
                __('Please use a real email address you can receive order updates at.')
            );
        }
    }

    /**
     * @throws LocalizedException
     */
    private function assertGmailPlusAllowed(string $email): void
    {
        if (preg_match('/^[^@]+\+[^@]+@gmail\.com$/', $email)) {
            throw new LocalizedException(
                __('Please use a real email address you can receive order updates at.')
            );
        }
    }

    private function isDomainBlocked(string $domain, ?int $storeId): bool
    {
        $blockedDomains = array_merge(
            self::BLOCKED_DOMAINS,
            $this->config->getExtraBlockedDomains($storeId)
        );

        if ($this->config->isExtendedEmailPatternsEnabled($storeId)) {
            $blockedDomains = array_merge($blockedDomains, self::EXTENDED_BLOCKED_DOMAINS);
        }

        return in_array($domain, array_unique($blockedDomains), true);
    }

    private function endsWith(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        $needleLength = strlen($needle);

        if ($needleLength > strlen($haystack)) {
            return false;
        }

        return substr($haystack, -$needleLength) === $needle;
    }
}
