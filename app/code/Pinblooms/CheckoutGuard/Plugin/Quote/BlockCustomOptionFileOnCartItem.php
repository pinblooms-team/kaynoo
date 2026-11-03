<?php
declare(strict_types=1);

namespace Pinblooms\CheckoutGuard\Plugin\Quote;

use Magento\Quote\Api\Data\CartItemInterface;
use Pinblooms\CheckoutGuard\Model\CustomOptionFileUploadGuard;

/**
 * Blocks REST/GraphQL cart item saves that include custom-option file_info.
 * Target: POST /V1/guest-carts/{id}/items and customer cart items.
 */
class BlockCustomOptionFileOnCartItem
{
    public function __construct(
        private readonly CustomOptionFileUploadGuard $uploadGuard
    ) {
    }

    /**
     * @return null
     */
    public function beforeSave($subject, CartItemInterface $cartItem)
    {
        $storeId = null;
        if (method_exists($cartItem, 'getStoreId')) {
            $storeId = (int) $cartItem->getStoreId() ?: null;
        }

        $this->uploadGuard->assertCartItemAllowed($cartItem, $storeId);

        return null;
    }
}
