<?php
/**
 * ForaStudio Private Limited.
 *
 * @category  ForaStudio
 * @package   ForaStudio_TrashManager
 * @author    ForaStudio
 * @copyright Copyright (c) 2018-2019 ForaStudio Private Limited (https://forastudio.com)
 */

namespace ForaStudio\TrashManager\Model\ResourceModel\CategoryTrash;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
	protected $_idFieldName = 'trash_id';
	/**
	 * Initialize resource
	 *
	 * @return void
	 */
	protected function _construct() {
		$this->_init(\ForaStudio\TrashManager\Model\CategoryTrash::class, \ForaStudio\TrashManager\Model\ResourceModel\CategoryTrash::class);
	}
}
