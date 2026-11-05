<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

use Magefan\Blog\Api\ShortContentExtractorInterface;

class ShortContentExtractor implements ShortContentExtractorInterface
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * @var array
     */
    private $executedContent = [];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        ?\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig = null
    ) {
        $this->filterProvider = $filterProvider;
        $this->scopeConfig = $scopeConfig ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
    }

    /**
     * Retrieve short filtered content
     * @param string $content
     * @param mixed $len
     * @param mixed $endCharacters
     * @return string
     * @throws \Exception
     */
    
    public function execute($content, $len = null, $endCharacters = null)
    {
        $content = (string)$content;
    
        $key = md5($content) . $len . $endCharacters;
        if (!isset($this->executedContent[$key])) {
    
            $content = $this->filterProvider->getPageFilter()->filter(
                (string) $content ?: ''
            );
    
            // Always sanitize the content to remove unwanted HTML tags and inline styles
            foreach (['style', 'script'] as $tagToRemove) {
                $content = preg_replace("~\<" . $tagToRemove . "(.*)\>(.*)\<\/" . $tagToRemove . "\>~", '', $content);
            }
            $content = html_entity_decode(strip_tags((string)$content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
            $content = $this->setPageBreakOnLen($content, $len);
    
            $pageBreaker = '<!-- pagebreak -->';
            $len = mb_strpos($content, $pageBreaker);
            if (!$len) {
                return $content;
            }
    
            $content = mb_substr($content, 0, $len);
    
            if ($endCharacters === null) {
                $endCharacters = $this->scopeConfig->getValue(
                    'mfblog/post_list/end_characters',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
            }
    
            if ($endCharacters) {
                $trimMask = " \t\n\r\0\x0B,.!?";
                $content = trim($content, $trimMask) . $endCharacters;
            }
    
            $this->executedContent[$key] = (string)$content;
        }
    
        return $this->executedContent[$key];
    }

    /**
     * @param string $content
     * @param $len
     * @return string
     */
    private function setPageBreakOnLen(string $content, $len): string
    {
        $pageBreaker = '<!-- pagebreak -->';
        if (!$len && false !== mb_strpos($content, $pageBreaker)) {
            /* No length and already has page breaker */
            return $content;
        }

        if (!$len) {
            $len = $this->getDefaultShortContentLength();
        }

        $content = str_replace($pageBreaker, '', $content);

        $previousErrorState = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->loadHTML('<?xml encoding="UTF-8">' . '<body>' . $content . '</body>');
        libxml_use_internal_errors($previousErrorState);

        $textLength = 0;

        $processNode = function ($node) use (&$textLength, $len, $dom, $pageBreaker, &$pageBreakInserted, &$processNode) {

            if ($pageBreakInserted) {
                return;
            }

            if ($node->childNodes) {
                foreach ($node->childNodes as $child) {
                    $processNode($child);
                }
            }

            if ($node->nodeType === XML_TEXT_NODE) {

                $text = $node->nodeValue;
                $words = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                $newText = '';

                foreach ($words as $word) {
                    $newText .= $word;

                    if (trim($word) === '') {
                        continue;
                    }

                    if ($textLength + mb_strlen($word, 'utf-8') >= $len && !$pageBreakInserted) {
                        $newText .= $pageBreaker;
                        $pageBreakInserted = true;
                    }

                    $textLength += mb_strlen($word, 'utf-8') + 1;
                }

                $node->nodeValue = $newText;
            }
        };

        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body) {
            $processNode($body);
        }

        $content = $dom->saveHTML($body);

        $content = preg_replace('#^<body>|</body>$#', '', $content);
        $content = str_replace('&lt;!-- pagebreak --&gt;', $pageBreaker, $content);
        return $content;
    }

    /**
     * @return int
     */
    private function getDefaultShortContentLength(): int
    {
        return (int)$this->scopeConfig->getValue(
            'mfblog/post_list/shortcotent_length',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?: 2000;
    }
}
