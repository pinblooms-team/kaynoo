<?php

namespace ForaStudio\TrashManager\Ui\Component\Listing\Column\Category;

use Magento\Catalog\Helper\Image;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Thumbnail
 */
class Thumbnail extends Column {
	const ALT_FIELD = 'name';

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $storeManager;

	/**
	 * @param ContextInterface $context
	 * @param UiComponentFactory $uiComponentFactory
	 * @param Image $imageHelper
	 * @param UrlInterface $urlBuilder
	 * @param StoreManagerInterface $storeManager
	 * @param array $components
	 * @param array $data
	 */
	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		Image $imageHelper,
		UrlInterface $urlBuilder,
		StoreManagerInterface $storeManager,
		array $components = [],
		array $data = []
	) {
		$this->storeManager = $storeManager;
		$this->imageHelper = $imageHelper;
		$this->urlBuilder = $urlBuilder;
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
				$data = json_decode($item['category_data'], true);
				$url = '';
				if (isset($data[$fieldName])) {
					$url = $this->storeManager->getStore()->getBaseUrl(
						\Magento\Framework\UrlInterface::URL_TYPE_MEDIA
					) . 'trashmanager/catalog/category/' . $data[$fieldName];
				}
				$item[$fieldName . '_src'] = $url;
				$item[$fieldName . '_alt'] = $this->getAlt($item) ?: '';
				$item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
					'trashmanager/category/index/',
					['trash_id' => $item['trash_id']]
				);
				$item[$fieldName . '_orig_src'] = $url;
			}
		}

		return $dataSource;
	}

	/**
	 * @param array $row
	 *
	 * @return null|string
	 */
	protected function getAlt($row) {
		$altField = $this->getData('config/altField') ?: self::ALT_FIELD;
		return isset($row[$altField]) ? $row[$altField] : null;
	}
}