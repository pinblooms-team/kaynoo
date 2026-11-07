<?php

declare(strict_types=1);

namespace Tecksky\Custom\Plugin\Review;

use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Model\Product;
use Magento\Review\Block\Product\ReviewRenderer;

/**
 * PHP 8.5: Magento indexes $_availableTemplates[$templateType].
 * Destino passes null; that is a deprecated array offset and becomes an exception in developer mode.
 */
class ReviewRendererPlugin
{
    public function aroundGetReviewsSummaryHtml(
        ReviewRenderer $subject,
        callable $proceed,
        Product $product,
        $templateType = ReviewRendererInterface::DEFAULT_VIEW,
        $displayIfNoReviews = false
    ) {
        if ($templateType === null || $templateType === '') {
            $templateType = ReviewRendererInterface::DEFAULT_VIEW;
        }

        return $proceed($product, $templateType, $displayIfNoReviews);
    }
}
