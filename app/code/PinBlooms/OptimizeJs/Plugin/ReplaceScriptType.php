<?php

namespace PinBlooms\OptimizeJs\Plugin;

use Psr\Log\LoggerInterface;

class ReplaceScriptType
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor function
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Ensure defer attribute is added to all script tags except .min.js files
     *
     * @param \Magento\Framework\View\Result\Page $subject
     * @param \Magento\Framework\App\ResponseInterface $response
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function afterRenderResult(\Magento\Framework\View\Result\Page $subject, $response)
    {
        // Get the body content from the response
        $body = $response->getBody();

        // Regex pattern to find all script tags
        $pattern = '/<script\s+([^>]*?)type\s*=\s*"text\/javascript"([^>]*)>(.*?)<\/script>/is';

        // Callback function to add defer attribute
        $callback = function ($matches) {
            $scriptTag = $matches[0];

            // Define patterns to exclude from defer attribute addition
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


            return $scriptTag;
        };

        // Apply the callback function to the response body
        $modifiedBody = preg_replace_callback($pattern, $callback, $body);

        // Log the original and modified body content (optional)
        $this->logger->info('Original Body: ' . $body);
        $this->logger->info('Modified Body: ' . $modifiedBody);

        // Set the modified content back to the response
        $response->setBody($modifiedBody);

        // Return the modified response
        return $response;
    }
}
