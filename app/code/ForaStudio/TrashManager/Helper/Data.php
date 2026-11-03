<?php
/**
 * ForaStudio Private Limited.
 *
 * @category  ForaStudio
 * @package   ForaStudio_TrashManager
 * @author    ForaStudio
 * @copyright Copyright (c) 2018-2019 ForaStudio Private Limited (https://forastudio.com)
 */
namespace ForaStudio\TrashManager\Helper;


use Magento\Framework\App\Helper\AbstractHelper;


/**
 * Class Data
 * @package ForaStudio\TrashManager\Helper
 */
class Data extends AbstractHelper {

	/**
	 * Config paths for using throughout the code
	 */
	const XML_PATH_PRODUCT_ENABLE = 'forastudio_trashmanager/product/enable';
	const XML_PATH_CATEGORY_ENABLE = 'forastudio_trashmanager/category/enable';
	const XML_PATH_CATEGORY_AUTO_CLEAN_DAYS = 'forastudio_trashmanager/category/clear_after_days';
	const XML_PATH_PRODUCT_AUTO_CLEAN_DAYS = 'forastudio_trashmanager/product/clear_after_days';

	/**
     * Get configuration value
     * @param string $config_path
     * @return string
     */
    protected function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

	/**
	 * Is product trash enabled
	 * @return int
	 */
	public function isProductTrashEnabled() {
		return $this->getConfig(self::XML_PATH_PRODUCT_ENABLE);
	}

	/**
	 * Is category trash enabled
	 * @return int
	 */
	public function isCategoryTrashEnabled() {
		return $this->getConfig(self::XML_PATH_CATEGORY_ENABLE);
	}

	/**
	 * Get configured number of days for product trash auto clean
	 * @return int
	 */
	public function getProductTrashAutoCleanDays() {
		return $this->getConfig(self::XML_PATH_PRODUCT_AUTO_CLEAN_DAYS);
	}

	/**
	 * Get configured number of days for categroy trash auto clean
	 * @return int
	 */
	public function getCategoryTrashAutoCleanDays() {
		return $this->getConfig(self::XML_PATH_CATEGORY_AUTO_CLEAN_DAYS);
	}
	
	
}
