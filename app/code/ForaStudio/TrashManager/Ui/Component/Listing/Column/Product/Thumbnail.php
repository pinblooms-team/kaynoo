<?php
/**
 * ForaStudio Private Limited.
 *
 * @category  ForaStudio
 * @package   ForaStudio_TrashManager
 * @author    ForaStudio
 * @copyright Copyright (c) 2018-2019 ForaStudio Private Limited (https://forastudio.com)
 */

namespace ForaStudio\TrashManager\Ui\Component\Listing\Column\Product;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use ForaStudio\TrashManager\Model\ProductTrash;

/**
 * Class Thumbnail
 */
class Thumbnail extends Column {
	
	/**
	 * @var UrlInterface
	 */
	protected $urlBuilder;

	/* @var StoreManagerInterface
	 */
	protected $storeManager;


	/**
	 * @param ContextInterface $context
	 * @param UiComponentFactory $uiComponentFactory
	 * @param UrlInterface $urlBuilder
	 * @param StoreManagerInterface $storeManager
	 * @param array $components
	 * @param array $data
	 */
	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		UrlInterface $urlBuilder,
		StoreManagerInterface $storeManager,
		array $components = [],
		array $data = []
	) {
		
		$this->urlBuilder = $urlBuilder;
		$this->storeManager = $storeManager;
		parent::__construct($context, $uiComponentFactory, $components, $data);
	}

	/**
	 * Prepare Data Source
	 *
	 * @param array $dataSource
	 * @return array
	 */
	public function prepareDataSource(array $dataSource) {
		if (isset($dataSource['data']['items'])) {
			$fieldName = $this->getData('name');
			foreach ($dataSource['data']['items'] as &$item) {
				$json_data = json_decode($item['product_data'], true);
				if(isset($json_data['images']) && isset($json_data['images'][0]))
				{
					$url = $this->storeManager->getStore()->getBaseUrl(
						\Magento\Framework\UrlInterface::URL_TYPE_MEDIA
					) . ProductTrash::ENTITY_MEDIA_PATH . $json_data['images'][0];
					$item[$fieldName . '_src'] = $url;
					$item[$fieldName . '_alt'] = $this->getAlt($item) ?: '';
					$item[$fieldName . '_link'] ='#';
				}
			}
		}

		return $dataSource;
	}
}