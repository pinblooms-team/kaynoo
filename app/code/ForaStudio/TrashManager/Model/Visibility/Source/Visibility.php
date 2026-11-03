<?php
/**
 * ForaStudio Private Limited.
 *
 * @category  ForaStudio
 * @package   ForaStudio_TrashManager
 * @author    ForaStudio
 * @copyright Copyright (c) 2018-2019 ForaStudio Private Limited (https://forastudio.com)
 */

namespace ForaStudio\TrashManager\Model\Visibility\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Visibility implements OptionSourceInterface {
	/**
	 * Get options
	 *
	 * @return array
	 */
	public function toOptionArray() {
		$option = [
			[
				'value' => 1,
				'label' => __('Not Visible Individually'),
			],
			[
				'value' => 2,
				'label' => __('Catalog'),
			],
			[
				'value' => 3,
				'label' => __('Search'),
			],
			[
				'value' => 4,
				'label' => __('Catalog, Search'),
			],
		];
		return $option;

	}
}