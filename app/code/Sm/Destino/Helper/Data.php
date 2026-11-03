<?php
/*------------------------------------------------------------------------
# SM Destino
# Copyright (c) 2016 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\Destino\Helper;
use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

	public function __construct(
		StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\App\Helper\Context $context
    ) {
		$this->_storeManager = $storeManagerInterface;
        parent::__construct($context);
    }
	
	public function getStoreId(){
		return $this->_storeManager->getStore()->getId();
	}
	
	public function getUrl(){
		return $this->_storeManager->getStore()->getBaseUrl();
	}
	
	public function getMediaUrl(){
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
	}
	
	public function getGeneral($name, $storeCode = null)
	{
        return $this->scopeConfig->getValue(
            'destino/general/'.$name,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$storeCode
        );		
	}
	
	public function getFont($name, $storeCode = null)
	{
        return $this->scopeConfig->getValue(
            'destino/font/'.$name,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$storeCode
        );		
	}
	
	public function getColor($name, $storeCode = null)
	{
        return $this->scopeConfig->getValue(
            'destino/color/'.$name,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$storeCode
        );		
	}	
	
	public function getPageWrapper($name, $storeCode = null)
	{
        return $this->scopeConfig->getValue(
            'destino/page_wrapper/'.$name,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$storeCode
        );		
	}

	public function getHeader($name, $storeCode = null)
	{
        return $this->scopeConfig->getValue(
            'destino/header/'.$name,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$storeCode
        );		
	}
	
	public function getMainContent($name, $storeCode = null)
	{
        return $this->scopeConfig->getValue(
            'destino/main_content/'.$name,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$storeCode
        );		
	}

	public function getFooter($name, $storeCode = null)
	{
        return $this->scopeConfig->getValue(
            'destino/footer/'.$name,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$storeCode
        );		
	}	
	
	public function getCategoryView($name, $storeCode = null)
	{
        return $this->scopeConfig->getValue(
            'destino/category_view/'.$name,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$storeCode
        );		
	}

	public function getProductDetail($name, $storeCode = null)
	{
        return $this->scopeConfig->getValue(
            'destino/product_detail/'.$name,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$storeCode
        );		
	}	
	
	public function getAdvanced($name, $storeCode = null)
	{
        return $this->scopeConfig->getValue(
            'destino/advanced/'.$name,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$storeCode
        );		
	}
	
}