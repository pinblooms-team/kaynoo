<?php
/**
 * ForaStudio Private Limited.
 *
 * @category  ForaStudio
 * @package   ForaStudio_TrashManager
 * @author    ForaStudio
 * @copyright Copyright (c) 2018-2019 ForaStudio Private Limited (https://forastudio.com)
 */

namespace ForaStudio\TrashManager\Controller\Adminhtml\Category;

use ForaStudio\TrashManager\Model\ResourceModel\CategoryTrash\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 */
class MassDelete extends \Magento\Backend\App\Action {
	/**
	 * Authorization level of a basic admin session
	 *
	 * @see _isAllowed()
	 */
	const ADMIN_RESOURCE = 'ForaStudio_TrashManager::category';

	/**
	 * @var Filter
	 */
	protected $_filter;

	/**
	 * @var CollectionFactory
	 */
	protected $_collectionFactory;

	/**
	 * @param Context $context
	 * @param Filter $filter
	 * @param CollectionFactory $collectionFactory
	 */
	public function __construct(
		Context $context,
		Filter $filter,
		CollectionFactory $collectionFactory
	) {
		$this->_filter = $filter;
		$this->_collectionFactory = $collectionFactory;
		parent::__construct($context);
	}

	/**
	 * Execute action
	 *
	 * @return \Magento\Backend\Model\View\Result\Redirect
	 * @throws \Magento\Framework\Exception\LocalizedException|\Exception
	 */
	public function execute() {
		$collection = $this->_filter->getCollection($this->_collectionFactory->create());
		$recordDeleted = 0;
		foreach ($collection as $record) {
			$record->delete();
			$recordDeleted++;
		}
		$this->messageManager->addSuccess(__('A total of %1 category(s) have been deleted.', $recordDeleted));
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		return $resultRedirect->setPath('*/*/');
	}
}