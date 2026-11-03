<?php

/**
 * ForaStudio Private Limited.
 *
 * @category  ForaStudio
 * @package   ForaStudio_TrashManager
 * @author    ForaStudio
 * @copyright Copyright (c) 2018-2019 ForaStudio Private Limited (https://forastudio.com)
 */
namespace ForaStudio\TrashManager\Ui\Component\Listing\Column\Category;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Path
 */
class Path extends Column {
	/**
	 * Prepare Data Source.
	 *
	 * @param array $dataSource
	 *
	 * @return array
	 */
	public function prepareDataSource(array $dataSource) {
		if (isset($dataSource['data']['items'])) {
			$fieldName = $this->getData('name');
			foreach ($dataSource['data']['items'] as &$item) {
				$data = json_decode($item['category_data'], true);
				if (isset($data[$fieldName])) {
					$item[$fieldName] = $data[$fieldName];
				}
			}
		}
		return $dataSource;
	}
}