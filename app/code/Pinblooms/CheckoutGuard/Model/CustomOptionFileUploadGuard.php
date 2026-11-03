<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Model;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Block custom-option file uploads from storefront/API bots only.
 * Adminhtml (product create/edit, catalog) is never blocked.
 */
class CustomOptionFileUploadGuard
{
    /**
     * Keys commonly present on Magento file custom-option / bot upload payloads.
     */
    private const FILE_VALUE_KEYS = [
        'type',
        'name',
        'fullpath',
        'quote_path',
        'order_path',
        'secret_key',
        'file_content',
        'base64_encoded_data',
        'tmp_name',
        'file',
    ];

    public function __construct(
        private readonly Config $config,
        private readonly GuardLogger $guardLogger,
        private readonly State $appState
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function assertCartItemAllowed(CartItemInterface $cartItem, ?int $storeId = null): void
    {
        if ($this->isAdminArea()) {
            return;
        }

        if (!$this->config->isCustomOptionFileUploadBlocked($storeId)) {
            return;
        }

        if (!$this->cartItemHasFileUpload($cartItem)) {
            return;
        }

        $this->guardLogger->logBlocked(
            'custom_option_file_upload',
            'Blocked custom option file upload (writes to media/custom_options).',
            $storeId,
            [
                'sku' => (string) $cartItem->getSku(),
                'quote_id' => $cartItem->getQuoteId(),
            ]
        );

        throw new LocalizedException(
            __('File uploads for product options are not allowed.')
        );
    }

    /**
     * @throws LocalizedException
     */
    public function assertFileOptionUploadBlocked(?int $storeId = null): void
    {
        if ($this->isAdminArea()) {
            return;
        }

        if (!$this->config->isCustomOptionFileUploadBlocked($storeId)) {
            return;
        }

        $this->guardLogger->logBlocked(
            'custom_option_file_upload',
            'Blocked Magento File custom-option upload processor.',
            $storeId,
            []
        );

        throw new LocalizedException(
            __('File uploads for product options are not allowed.')
        );
    }

    private function isAdminArea(): bool
    {
        try {
            return $this->appState->getAreaCode() === Area::AREA_ADMINHTML;
        } catch (\Throwable $e) {
            // Area not set yet (some CLI) — treat as non-admin only for cart/API paths.
            return false;
        }
    }

    private function cartItemHasFileUpload(CartItemInterface $cartItem): bool
    {
        $productOption = $cartItem->getProductOption();
        if ($productOption === null) {
            return false;
        }

        $extension = $productOption->getExtensionAttributes();
        if ($extension === null || !method_exists($extension, 'getCustomOptions')) {
            return false;
        }

        $customOptions = $extension->getCustomOptions();
        if (!is_array($customOptions) && !($customOptions instanceof \Traversable)) {
            return false;
        }

        foreach ($customOptions as $customOption) {
            if (!is_object($customOption)) {
                continue;
            }

            // Primary signal: file_info extension attribute (no file-processor side effects).
            try {
                $optExt = method_exists($customOption, 'getExtensionAttributes')
                    ? $customOption->getExtensionAttributes()
                    : null;
                if ($optExt !== null && method_exists($optExt, 'getFileInfo') && $optExt->getFileInfo()) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Inspection must never break a real customer's cart save.
            }

            // Secondary: inspect raw option value without casting arrays to string.
            // Prefer getData() so overridden getters cannot trigger Magento file processors.
            try {
                if ($this->looksLikeFileOptionValue($this->readOptionValueSafely($customOption))) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Skip this option; BlockFileCustomOptionType still covers real File processors.
                continue;
            }
        }

        return false;
    }

    /**
     * Read option value without forcing array→string (CRITICAL noise) and without
     * invoking Magento file-upload processors where possible.
     *
     * @param object $customOption
     * @return mixed
     */
    private function readOptionValueSafely(object $customOption)
    {
        if ($customOption instanceof DataObject) {
            return $customOption->getData('option_value');
        }

        if (method_exists($customOption, 'getOptionValue')) {
            return $customOption->getOptionValue();
        }

        return null;
    }

    /**
     * True only for file-upload shaped payloads. Normal text/select/date values stay false
     * so real customer cart items are never blocked by this check.
     *
     * @param mixed $optionValue
     */
    private function looksLikeFileOptionValue($optionValue): bool
    {
        if ($optionValue === null || $optionValue === '') {
            return false;
        }

        if (is_array($optionValue)) {
            foreach (self::FILE_VALUE_KEYS as $key) {
                if (array_key_exists($key, $optionValue)) {
                    return true;
                }
            }

            return false;
        }

        if (is_object($optionValue)) {
            if ($optionValue instanceof DataObject) {
                foreach (self::FILE_VALUE_KEYS as $key) {
                    if ($optionValue->hasData($key)) {
                        return true;
                    }
                }
            }

            return method_exists($optionValue, 'getName')
                && method_exists($optionValue, 'getType');
        }

        if (is_string($optionValue) || is_numeric($optionValue)) {
            // Keep prior bot pattern where option_value was literally "file".
            return strcasecmp((string) $optionValue, 'file') === 0;
        }

        return false;
    }
}
