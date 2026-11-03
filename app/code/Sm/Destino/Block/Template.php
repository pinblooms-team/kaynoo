<?php
namespace Sm\Destino\Block;

class Template extends \Magento\Framework\View\Element\Template {
    public $_coreRegistry;
	
	public function _prepareLayout(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$helper_config = $objectManager->get('Sm\Destino\Helper\Data');
		
		/* $header_style = $helper_config->getHeader('header_style')); */
		
		$headerStyle = $helper_config->getHeader('header_style');
		$homeStyle = $helper_config->getGeneral('home_style');
		$footerStyle = $helper_config->getFooter('footer_style');
		$layout = $helper_config->getGeneral('layout_style');
		$product_hover = $helper_config->getCategoryView('hover_image_effect');

		$right_to_left = $helper_config->getGeneral('rtl_layout');
		
		if($right_to_left){
			$rtl = 'rtl-layout';
		} else {
			$rtl = '';
		}
		
		//$theme_class = '' . $headerStyle . '-style ' . $space . $homeStyle . '-style ';
		
		$this->pageConfig->addBodyClass($headerStyle . '-style');
		$this->pageConfig->addBodyClass($homeStyle . '-style');
		$this->pageConfig->addBodyClass($footerStyle . '-style');
		$this->pageConfig->addBodyClass($layout . '-layout');
		$this->pageConfig->addBodyClass($product_hover . '-hover-effect');
		$this->pageConfig->addBodyClass($rtl);
		
		return parent::_prepareLayout();
	}
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }    
}
?>