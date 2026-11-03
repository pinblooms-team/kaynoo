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
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Putback
 */
class Putback extends \Magento\Backend\App\Action {
	
	/**
	 * Authorization level of a basic admin session
	 *
	 * @see _isAllowed()
	 */
	const ADMIN_RESOURCE = 'ForaStudio_TrashManager::category';
	
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
	 * @param Context  		$context
	 * @param PageFactory   $resultPageFactory
	 * @param CategoryTrash $categoryTrash
	 */
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		
		CategoryTrash $categoryTrash,
		\ForaStudio\TrashManager\Helper\Data $helper
	) {
		$this->_resultPageFactory = $resultPageFactory;
		$this->_categoryTrash = $categoryTrash;
		parent::__construct($context);
	}

	/**
	 * Put back category to catalog data
	 *
	 * @return \Magento\Backend\Model\View\Result\Redirect
	 */
	public function execute() {
		$trashId = $this->getRequest()->getParam('trash_id',false);
		if ($trashId) {
			try {
				$this->_categoryTrash->load($trashId);
				$this->putBack($this->_categoryTrash);
				$this->messageManager->addSuccess(__('The Category has been restored Successfully.'));
				$this->_getSession()->setFormData(false);
			} catch (\Magento\Framework\Exception\LocalizedException $e) {
				$this->messageManager->addError($e->getMessage());
			} catch (\Exception $e) {
				$this->messageManager->addException($e,__('Something gone wrong when putting back category: '.$e->getMessage()));
			}
		}
		else{
			$this->messageManager->addError(__('Trash Id not found'));
		}
		$this->_redirect('*/category');
	}

	protected function putBack($trashModel)
	{
		if($trashModel->getParentId() && !$trashModel->isCategoryExistInCatalog($trashModel->getParentId()))
		{
			$collection = $trashModel->getCollection()->addFieldToFilter('entity_id',$trashModel->getParentId());
			if($collection->count() && $collection->getFirstItem()->getId()){
				$this->putBack($collection->getFirstItem()->getId());
			}
		}
		if(!$trashModel->isCategoryExistInCatalog($trashModel->getEntityId()))
		{
			$trashModel->putBack();
		}
		
	}
}