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

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class QuantityData
 */
class QuantityData extends Column 
{
	const ALT_FIELD = 'name';

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $storeManager;

	/**
	 * @param ContextInterface $context
	 * @param UiComponentFactory $uiComponentFactory
	 * @param StoreManagerInterface $storeManager
	 * @param array $components
	 * @param array $data
	 */
	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		StoreManagerInterface $storeManager,
		array $components = [],
		array $data = []
	) {
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
			$quantityData = $this->getData('name');
			foreach ($dataSource['data']['items'] as &$item) {
				$json_data = json_decode($item['product_data'], true);
				if(isset( $json_data['cataloginventory_stock_item']) && is_array( $json_data['cataloginventory_stock_item']))
				{
					$item[$quantityData] = $json_data['cataloginventory_stock_item'][0]['qty'];	
				}
			}
		}

		return $dataSource;
	}

}