<?php

namespace PinBlooms\OptimizeJs\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ReplaceScriptTypeObserver implements ObserverInterface
{
    /**
     * Modify the response body to replace script type and add defer attribute
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $observer->getResponse();

        // Get the response body
        $body = $response->getBody();

        // Regex pattern to find all script tags with type="text/javascript"
        $pattern = '/<script\s+([^>]*?)type\s*=\s*"text\/javascript"([^>]*)>(.*?)<\/script>/is';

        // Callback function to modify script tags
        $callback = function ($matches) {
            $scriptTag = $matches[0];

            // Define patterns to exclude from lazyload_int replacement and defer attribute addition
            $excludedPatterns = [
                '.min.js'
            ];

            // Check if any excluded pattern matches
            foreach ($excludedPatterns as $pattern) {
                if (strpos($scriptTag, $pattern) !== false) {
                    // Return the script tag as is for excluded patterns
                    return $scriptTag;
                }
            }

            // Replace type for non-excluded scripts
            $scriptTag = str_replace('type="text/javascript"', 'type="lazyload_int"', $scriptTag);

            return $scriptTag;
        };

        // Apply the callback function to the response body
        $modifiedBody = preg_replace_callback($pattern, $callback, $body);

        // Set the modified content back to the response
        $response->setBody($modifiedBody);
    }
}
