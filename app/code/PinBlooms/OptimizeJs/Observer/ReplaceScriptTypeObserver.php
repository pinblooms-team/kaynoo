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

        // Magento 2.4.9: rewriting scripts to lazyload_int delays require()/mage-init
        // until a gesture (or breaks DOMContentLoaded), so add-to-cart never binds.
        // Leave HTML unchanged; third-party defer can be re-enabled with Magepack later.
        return;

        // Get the response body
        $body = $response->getBody();

        // Regex pattern to find all script tags with type="text/javascript"
        $pattern = '/<script\s+([^>]*?)type\s*=\s*"text\/javascript"([^>]*)>(.*?)<\/script>/is';

        // Callback function to modify script tags
        $callback = function ($matches) {
            $scriptTag = $matches[0];

            // Inline Magento require()/mage-init must run immediately or add-to-cart never binds.
            if (!preg_match('/\ssrc\s*=/i', $scriptTag)) {
                return $scriptTag;
            }

            $excludedPatterns = [
                '.min.js',
                'w3_loadscripts',
                '_cache/merged/',
                'mage/requirejs',
                'requirejs-config',
                'require.js',
                'mixins.js',
                'Magento_Catalog',
                'Magento_Checkout',
                'Magento_Customer',
                'Magento_Ui',
                'Magento_PageCache',
                'catalogAddToCart',
                'mage/cookies',
                'form-key',
            ];

            foreach ($excludedPatterns as $exclude) {
                if (stripos($scriptTag, $exclude) !== false) {
                    return $scriptTag;
                }
            }

            $scriptTag = str_replace('type="text/javascript"', 'type="lazyload_int"', $scriptTag);

            return $scriptTag;
        };

        $modifiedBody = preg_replace_callback($pattern, $callback, $body);
        $response->setBody(is_string($modifiedBody) ? $modifiedBody : $body);
    }
}
