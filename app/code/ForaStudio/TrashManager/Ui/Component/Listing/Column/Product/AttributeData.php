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
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class AttributeData
 */
class AttributeData extends Column {
	
	/**
	 * @param ContextInterface $context
	 * @param UiComponentFactory $uiComponentFactory
	 * @param array $components
	 * @param array $data
	 */
	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		array $components = [],
		array $data = []
	) {
	
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
			$attributeName = $this->getData('name');
			/*$fieldName = $this->getData('')*/
			foreach ($dataSource['data']['items'] as &$item) {
				$json_data = json_decode($item['product_data'], true);
				$item[$attributeName] = $json_data['attribute_set'];
			}
		}

		return $dataSource;
	}
}