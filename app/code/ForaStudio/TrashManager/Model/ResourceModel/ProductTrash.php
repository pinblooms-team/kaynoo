<?php
/**
 * ForaStudio Private Limited.
 *
 * @category  ForaStudio
 * @package   ForaStudio_TrashManager
 * @author    ForaStudio
 * @copyright Copyright (c) 2018-2019 ForaStudio Private Limited (https://forastudio.com)
 */

namespace ForaStudio\TrashManager\Model\ResourceModel;

class ProductTrash extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	/**
	 * Constructor
	 *
	 * @param \Magento\Framework\Model\ResourceModel\Db\Context $context,
	 * @param string $connectionName
	 */
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context,
		$connectionName = null
	) {
		parent::__construct($context, $connectionName);
	}

	/**
	 * Define Main Table
	 *
	 * @return void
	 */
	protected function _construct() {
		$this->_init('fora_product_trash', 'trash_id');
	}
}