<?php

namespace ForaStudio\TrashManager\Model\ResourceModel\ProductTrash;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
	protected $_idFieldName = 'trash_id';
	/**
	 * Initialize resource
	 *
	 * @return void
	 */
	protected function _construct() {
		$this->_init(\ForaStudio\TrashManager\Model\ProductTrash::class, \ForaStudio\TrashManager\Model\ResourceModel\ProductTrash::class);
	}
}