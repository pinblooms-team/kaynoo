<?php
namespace Pinblooms\AdminDefaultComments\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    private const XML_BASE = 'pinblooms_default_comments/texts/';

    public function get(string $key): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_BASE . $key . '_text',
            ScopeInterface::SCOPE_STORE
        );
    }
}
