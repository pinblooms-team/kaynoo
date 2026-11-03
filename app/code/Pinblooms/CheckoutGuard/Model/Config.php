<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'pinblooms_checkout_guard/general/enabled';
    private const XML_PATH_LOGGING_ENABLED = 'pinblooms_checkout_guard/general/enable_logging';
    private const XML_PATH_LOG_CLIENT_IP = 'pinblooms_checkout_guard/general/log_client_ip';
    private const XML_PATH_LOG_USER_AGENT = 'pinblooms_checkout_guard/general/log_user_agent';
    private const XML_PATH_LOG_GEO = 'pinblooms_checkout_guard/general/log_geo';

    private const XML_PATH_BLOCK_GUEST_EMAIL =
        'pinblooms_checkout_guard/email/block_disposable_guest_email';
    private const XML_PATH_EXTENDED_EMAIL_PATTERNS =
        'pinblooms_checkout_guard/email/extended_email_patterns';
    private const XML_PATH_BLOCK_GMAIL_PLUS =
        'pinblooms_checkout_guard/email/block_gmail_plus_addressing';
    private const XML_PATH_EXTRA_BLOCKED_DOMAINS =
        'pinblooms_checkout_guard/email/extra_blocked_domains';

    private const XML_PATH_RESTRICT_GUEST_CHECKMO =
        'pinblooms_checkout_guard/checkmo/require_login_for_guests';

    private const XML_PATH_ENFORCE_GUEST_CHECKOUT =
        'pinblooms_checkout_guard/guest_checkout/enforce_magento_setting';

    private const XML_PATH_BLOCK_ADDRESS_INJECTION =
        'pinblooms_checkout_guard/address/block_template_injection';

    private const XML_PATH_RATE_LIMIT_ENABLED =
        'pinblooms_checkout_guard/rate_limit/enabled';
    private const XML_PATH_RATE_LIMIT_MAX =
        'pinblooms_checkout_guard/rate_limit/max_attempts';
    private const XML_PATH_RATE_LIMIT_WINDOW =
        'pinblooms_checkout_guard/rate_limit/window_seconds';
    private const XML_PATH_RATE_LIMIT_MAX_CART =
        'pinblooms_checkout_guard/rate_limit/max_attempts_per_cart';

    private const XML_PATH_PAYMENT_FAILED_BOT_SUPPRESS =
        'pinblooms_checkout_guard/payment_failed_email/suppress_bot_emails';
    private const XML_PATH_PAYMENT_FAILED_SKIP_TEMPLATE =
        'pinblooms_checkout_guard/payment_failed_email/skip_template_address';
    private const XML_PATH_PAYMENT_FAILED_SKIP_ZERO_TOTAL =
        'pinblooms_checkout_guard/payment_failed_email/skip_zero_total';
    private const XML_PATH_PAYMENT_FAILED_SKIP_EMPTY_GUEST =
        'pinblooms_checkout_guard/payment_failed_email/skip_empty_guest_name';
    private const XML_PATH_PAYMENT_FAILED_SKIP_JUNK_GUEST_EMAIL =
        'pinblooms_checkout_guard/payment_failed_email/skip_junk_guest_email';

    private const XML_PATH_BLOCK_CUSTOM_OPTION_FILE_UPLOAD =
        'pinblooms_checkout_guard/custom_option_file/block_uploads';

    private const MAGENTO_GUEST_CHECKOUT = 'checkout/options/guest_checkout';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isLoggingEnabled(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_LOGGING_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isLogClientIpEnabled(?int $storeId = null): bool
    {
        return $this->isLoggingEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_LOG_CLIENT_IP,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isLogUserAgentEnabled(?int $storeId = null): bool
    {
        return $this->isLoggingEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_LOG_USER_AGENT,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isLogGeoEnabled(?int $storeId = null): bool
    {
        return $this->isLoggingEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_LOG_GEO,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isGuestEmailBlockingEnabled(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_BLOCK_GUEST_EMAIL,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isExtendedEmailPatternsEnabled(?int $storeId = null): bool
    {
        return $this->isGuestEmailBlockingEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_EXTENDED_EMAIL_PATTERNS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isGmailPlusBlockingEnabled(?int $storeId = null): bool
    {
        return $this->isGuestEmailBlockingEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_BLOCK_GMAIL_PLUS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    /**
     * @return string[]
     */
    public function getExtraBlockedDomains(?int $storeId = null): array
    {
        $raw = (string) $this->scopeConfig->getValue(
            self::XML_PATH_EXTRA_BLOCKED_DOMAINS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($raw === '') {
            return [];
        }

        $domains = preg_split('/[\s,]+/', strtolower($raw), -1, PREG_SPLIT_NO_EMPTY);

        return is_array($domains) ? array_values(array_unique($domains)) : [];
    }

    public function isGuestCheckmoRestricted(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_RESTRICT_GUEST_CHECKMO,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isGuestCheckoutEnforcementEnabled(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENFORCE_GUEST_CHECKOUT,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isMagentoGuestCheckoutAllowed(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::MAGENTO_GUEST_CHECKOUT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isAddressInjectionBlockingEnabled(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_BLOCK_ADDRESS_INJECTION,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isRateLimitEnabled(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_RATE_LIMIT_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function getRateLimitMaxAttempts(?int $storeId = null): int
    {
        $value = (int) $this->scopeConfig->getValue(
            self::XML_PATH_RATE_LIMIT_MAX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $value > 0 ? $value : 25;
    }

    public function getRateLimitWindowSeconds(?int $storeId = null): int
    {
        $value = (int) $this->scopeConfig->getValue(
            self::XML_PATH_RATE_LIMIT_WINDOW,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $value > 0 ? $value : 60;
    }

    public function getRateLimitMaxAttemptsPerCart(?int $storeId = null): int
    {
        $value = (int) $this->scopeConfig->getValue(
            self::XML_PATH_RATE_LIMIT_MAX_CART,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $value > 0 ? $value : 40;
    }

    public function isPaymentFailedBotEmailSuppressEnabled(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_PAYMENT_FAILED_BOT_SUPPRESS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isPaymentFailedSkipTemplateAddressEnabled(?int $storeId = null): bool
    {
        return $this->isPaymentFailedBotEmailSuppressEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_PAYMENT_FAILED_SKIP_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isPaymentFailedSkipZeroTotalEnabled(?int $storeId = null): bool
    {
        return $this->isPaymentFailedBotEmailSuppressEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_PAYMENT_FAILED_SKIP_ZERO_TOTAL,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isPaymentFailedSkipEmptyGuestNameEnabled(?int $storeId = null): bool
    {
        return $this->isPaymentFailedBotEmailSuppressEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_PAYMENT_FAILED_SKIP_EMPTY_GUEST,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isPaymentFailedSkipJunkGuestEmailEnabled(?int $storeId = null): bool
    {
        return $this->isPaymentFailedBotEmailSuppressEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_PAYMENT_FAILED_SKIP_JUNK_GUEST_EMAIL,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isCustomOptionFileUploadBlocked(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_BLOCK_CUSTOM_OPTION_FILE_UPLOAD,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
}
