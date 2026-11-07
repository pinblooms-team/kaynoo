<?php

declare(strict_types=1);

namespace Tecksky\Custom\Plugin\Yireo;

use Yireo\NextGenImages\Util\HtmlReplacer;

/**
 * PHP 8+: preg_replace_callback() can return null (PCRE backtrack limit)
 * on large category/product HTML. NextGenImages then TypeErrors.
 */
class HtmlReplacerPlugin
{
    public function aroundReplace(HtmlReplacer $subject, callable $proceed, string $html): string
    {
        try {
            $result = $proceed($html);
            return is_string($result) ? $result : $html;
        } catch (\TypeError $e) {
            return $html;
        }
    }
}
