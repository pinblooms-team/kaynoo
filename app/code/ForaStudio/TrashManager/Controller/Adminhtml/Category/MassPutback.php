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

use ForaStudio\TrashManager\Model\CategoryTrash;
use ForaStudio\TrashManager\Model\ResourceModel\CategoryTrash\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassPutback
 */
class MassPutback extends \Magento\Backend\App\Action {

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
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $_resultPageFactory;

	/**
	 * @var CategoryTrash
	 */
	protected $_categoryTrash;

	/**
	 * Construct
	 * @param Context                                     $context
	 * @param PageFactory                                 $resultPageFactory
	 * @param CategoryTrash                               $categoryTrash
	 * @param Filter                                      $filter
	 * @param CollectionFactory                           $collectionFactory
	 */
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		CategoryTrash $categoryTrash,
		Filter $filter,
		CollectionFactory $collectionFactory
	) {
		$this->_collectionFactory = $collectionFactory;
		$this->_filter = $filter;
		$this->_resultPageFactory = $resultPageFactory;
		$this->_categoryTrash = $categoryTrash;
		parent::__construct($context);
	}

	/**
	 * Delete Banner Template
	 *
	 * @return void
	 */
	public function execute() {
		$trashCollection = $this->_filter->getCollection($this->_collectionFactory->create());
		try {
			$count=0;
			foreach ($trashCollection as $trashModel) {
				$this->putBack($trashModel);
				$count++;
			}
			$this->messageManager->addSuccess(__('The  %1 categories has been restored successfully.',$count));
			$this->_getSession()->setFormData(false);
		} catch (\Magento\Framework\Exception\LocalizedException $e) {
			$this->messageManager->addError($e->getMessage());
		} catch (\Exception $e) {
			$this->messageManager->addException($e,__('Something gone wrong when putting back categories: '.$e->getMessage()));
		}
		$this->_redirect('*/category');
	}

	protected function putBack($trashModel)
	{
		if($trashModel->getParentId() && !$trashModel->isCategoryExistInCatalog($trashModel->getParentId()))
		{
			$collection = $trashModel->getCollection()->addFieldToFilter('entity_id',$trashModel->getParentId());
			if($collection->count() && $collection->getFirstItem()->getId()){
				$this->putBack($collection->getFirstItem());
			}
		}
		if(!$trashModel->isCategoryExistInCatalog($trashModel->getEntityId()))
		{
			$trashModel->putBack();
		}
		
	}
}