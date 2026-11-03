<?php
/**
 * ForaStudio Private Limited.
 *
 * @category  ForaStudio
 * @package   ForaStudio_TrashManager
 * @author    ForaStudio
 * @copyright Copyright (c) 2018-2019 ForaStudio Private Limited (https://forastudio.com)
 */

namespace ForaStudio\TrashManager\Controller\Adminhtml\Product;

use ForaStudio\TrashManager\Model\ResourceModel\ProductTrash\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use ForaStudio\TrashManager\Model\ProductTrash;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class MassPutback
 */
class MassPutback extends \Magento\Backend\App\Action {
	
	/**
	 * Authorization level of a basic admin session
	 *
	 * @see _isAllowed()
	 */
	const ADMIN_RESOURCE = 'ForaStudio_TrashManager::product';

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
	protected $_productTrash;

	/**
	 * Construct
	 * @param Context 			$context
	 * @param PageFactory   	$resultPageFactory
	 * @param ProductTrash  	$productTrash
	 * @param Filter        	$filter
	 * @param CollectionFactory $collectionFactory
	 */
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		ProductTrash $productTrash,
		Filter $filter,
		CollectionFactory $collectionFactory
	) {
		$this->_collectionFactory = $collectionFactory;
		$this->_filter = $filter;
		$this->_resultPageFactory = $resultPageFactory;
		$this->_productTrash = $productTrash;
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
				$trashModel->putBack();
				$count++;
			}
			$this->messageManager->addSuccess(__('The  %1 products has been restored successfully.',$count));
			$this->_getSession()->setFormData(false);
		} catch (\Magento\Framework\Exception\LocalizedException $e) {
			$this->messageManager->addError($e->getMessage());
		} catch (\Exception $e) {
			$this->messageManager->addException($e,__('Something gone wrong when putting back products: '.$e->getMessage()));
		}
		$this->_redirect('*/product');
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
