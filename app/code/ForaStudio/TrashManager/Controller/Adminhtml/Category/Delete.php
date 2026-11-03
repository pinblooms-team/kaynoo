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
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Delete
 */
class Delete extends \Magento\Backend\App\Action {
	
	const ADMIN_RESOURCE = 'ForaStudio_TrashManager::category';
	
	/**
	 * Core registry
	 *
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry;

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
	 * @param Context       $context
	 * @param PageFactory   $resultPageFactory
	 * @param Registry      $registry
	 * @param CategoryTrash $categoryTrash
	 */
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		Registry $registry,
		CategoryTrash $categoryTrash
	) {
		$this->_resultPageFactory = $resultPageFactory;
		$this->_coreRegistry = $registry;
		$this->_categoryTrash = $categoryTrash;
		parent::__construct($context);
	}

	/**
	 * Delete Banner Template
	 *
	 * @return void
	 */
	public function execute() {
		$trashModel = $this->_categoryTrash->load($this->getRequest()->getParam('trash_id'));
		if ($trashModel->getId()) {
			try {
				$trashModel->delete();
				$this->messageManager->addSuccess(__('The Category has been deleted permenently.'));
				$this->_getSession()->setFormData(false);
			} catch (\Magento\Framework\Exception\LocalizedException $e) {
				$this->messageManager->addError($e->getMessage());
			} catch (\Exception $e) {
				$this->messageManager->addException($e, __('We can\'t delete this category right now.'));
			}
		}
		$this->_redirect('*/category');
	}
}