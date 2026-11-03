<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Plugin\Catalog;

use Pinblooms\CheckoutGuard\Model\CustomOptionFileUploadGuard;

/**
 * Blocks Magento File custom-option processing (frontend multipart + API processors).
 */
class BlockFileCustomOptionType
{
    public function __construct(
        private readonly CustomOptionFileUploadGuard $uploadGuard
    ) {
    }

    /**
     * @param mixed $subject
     * @param mixed $values
     * @return array
     */
    public function beforeValidateUserValue($subject, $values)
    {
        $storeId = null;
        if (method_exists($subject, 'getConfigurationItem') && $subject->getConfigurationItem()) {
            $storeId = (int) $subject->getConfigurationItem()->getStoreId() ?: null;
        }

        $this->uploadGuard->assertFileOptionUploadBlocked($storeId);

        return [$values];
    }
}
