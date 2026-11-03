<?php
/*
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_SmPatch
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 *
 */
namespace Ced\SmPatch\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Serialize\Serializer\Json as Unserialize;
use Magento\Framework\View\DesignExceptions as InitialDesignExceptions;

class DesignExceptions extends InitialDesignExceptions
{
    /**
     * @var Detect
     */
    private $detect;

    /**
     * @var Redirect
     */
    private $redirect;

    /**
     * @var string
     */
    private $userAgent;

    /**
     * @var Unserialize
     */
    private $unSerialize;

    /**
     * DesignExceptions constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param string $exceptionConfigPath
     * @param string $scopeType
     * @param Detect $detect
     * @param Redirect $redirect
     * @param Unserialize $unSerialize
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $exceptionConfigPath,
        $scopeType,
        Unserialize $unSerialize
    ) {
        parent::__construct($scopeConfig, $exceptionConfigPath, $scopeType);
        $this->unSerialize = $unSerialize;
    }

    /**
     * @param $subject
     * @param $proceed
     * @param HttpRequest $request
     * @return bool|string
     * @SuppressWarnings("unused")
     */
    // @codingStandardsIgnoreLine
    public function aroundGetThemeByRequest($subject, callable $proceed, HttpRequest $request)
    {
        $rules = $proceed($request);

        $userAgent = $request->getServer('HTTP_USER_AGENT');

        if (empty($userAgent)) {
            return $rules;
        } 
 
        if(strpos($request->getPathInfo(), 'csmarketplace') == false &&
            strpos($request->getPathInfo(), 'csproduct') == false &&
            strpos($request->getPathInfo(), 'csvendorproductattribute') == false &&
            strpos($request->getPathInfo(), 'cstransaction') == false &&
            strpos($request->getPathInfo(), 'csmultiseller') == false &&
            strpos($request->getPathInfo(), 'csmultishipping') == false &&
            strpos($request->getPathInfo(), 'csimportexport') == false &&
            strpos($request->getPathInfo(), 'csmembership') == false &&
            strpos($request->getPathInfo(), 'csmobidelivery') == false &&
            strpos($request->getPathInfo(), 'csmessaging') == false &&
            strpos($request->getPathInfo(), 'csauction') == false &&
            strpos($request->getPathInfo(), 'csorder') == false){
            return $rules;
        }
        if(strpos($request->getPathInfo(), 'vshops') !== false){
            return $rules;            
        }
        $this->userAgent = $userAgent;

        $expressions = $this->scopeConfig->getValue(
            $this->exceptionConfigPath,
            $this->scopeType
        );

        $pos_android = strpos($userAgent, 'Android');
        $pos_iphone = strpos($userAgent, 'iPhone');
        $pos_iPod = strpos($userAgent, 'iPod');
        $pos_BlackBerry = strpos($userAgent, 'BlackBerry');
        $pos_Windows_Mobile = strpos($userAgent, 'Windows Mobile');
        $pos_Safari_Mobile = strpos($userAgent, 'Safari Mobile');

        if ($pos_android !== false ||
            $pos_iphone !== false ||
            $pos_iPod !== false ||
            $pos_BlackBerry !== false ||
            $pos_Windows_Mobile !== false ||
            $pos_Safari_Mobile !== false
        ) {
            $themeId = 9; 
            try{
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $_theme = $objectManager->create(\Magento\Theme\Model\Theme::class);
                $_Config = $objectManager->create(\Magento\Framework\App\Config\ScopeConfigInterface::class);
                $data = $_Config->getValue('ced_csmarketplace/general/theme', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $thd = $_theme->getCollection()->addFieldToFilter('theme_path', $data);
                if($themeId = $thd->getFirstItem()->getId()){ 
                    return $themeId;
                }
            }catch(\Exception $e){
                $themeId = 9;
            }
            return $themeId;
        }

        if (!$expressions) {
            return $rules;
        }

        $expressions = $this->unSerialize->unserialize($expressions);

        foreach ($expressions as $rule) {
            if (preg_match($rule['regexp'], $userAgent)) {
                return $rule['value'];
            }
        }

        return $rules;
    }
    /**
 * Remove repeated slashes from the start of the path.
 *
 * @param string $pathInfo
 * @return string
 */
    private function removeRepeatedSlashes($pathInfo)
    {
        $firstChar = (string)substr($pathInfo, 0, 1);
        if ($firstChar == '/') {
            $pathInfo = '/' . ltrim($pathInfo, '/');
        }

        return $pathInfo;
    }
}
