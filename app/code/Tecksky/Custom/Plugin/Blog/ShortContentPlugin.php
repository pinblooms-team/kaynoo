<?php

declare(strict_types=1);

namespace Tecksky\Custom\Plugin\Blog;

use Magefan\Blog\Model\Post;

/**
 * Listing excerpts must be plain text. Page Builder HTML ships unscoped
 * <style> (ul li::before) that leaks into the header on /blog.
 */
class ShortContentPlugin
{
    private const MAX_LENGTH = 280;

    public function afterGetShortFilteredContentWithoutImages(Post $subject, $result): string
    {
        return $this->toPlainExcerpt((string)$result);
    }

    public function afterGetShortFilteredContent(Post $subject, $result): string
    {
        return $this->toPlainExcerpt((string)$result);
    }

    private function toPlainExcerpt(string $html): string
    {
        $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html) ?? $html;
        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? $html;
        $text = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        if (mb_strlen($text) > self::MAX_LENGTH) {
            $text = rtrim(mb_substr($text, 0, self::MAX_LENGTH), " \t\n\r,;:.-") . '…';
        }
        return $text;
    }
}
