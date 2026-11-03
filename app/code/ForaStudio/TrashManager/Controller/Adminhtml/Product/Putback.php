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

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use ForaStudio\TrashManager\Model\ProductTrash;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Putback
 */
class Putback extends \Magento\Backend\App\Action {

	/**
	 * Authorization level of a basic admin session
	 *
	 * @see _isAllowed()
	 */
	const ADMIN_RESOURCE = 'ForaStudio_TrashManager::product';

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $_resultPageFactory;

	/**
	 * @var ProductTrash
	 */
	protected $_productTrash;

	/**
	 * Construct
	 * @param Context  		$context
	 * @param PageFactory   $resultPageFactory
	 * @param ProductTrash	$productTrash
	 */
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		ProductTrash $productTrash,
		\ForaStudio\TrashManager\Helper\Data $helper
	) {
		$this->_resultPageFactory = $resultPageFactory;
		$this->_productTrash = $productTrash;
		parent::__construct($context);
	}
	
	/**
	 * Execute action
	 *
	 * @return \Magento\Backend\Model\View\Result\Redirect
	 * @throws \Magento\Framework\Exception\LocalizedException|\Exception
	 */
	public function execute() {


		$trashId = $this->getRequest()->getParam('trash_id',false);
		if ($trashId) {
			try {
				$this->_productTrash->load($trashId);
				$this->_productTrash->putBack();
				$this->messageManager->addSuccess(__('The Product has been restored Successfully.'));
				$this->_getSession()->setFormData(false);
			} catch (\Magento\Framework\Exception\LocalizedException $e) {
				$this->messageManager->addError($e->getMessage());
			} catch (\Exception $e) {
				$this->messageManager->addException($e,__('Something gone wrong when putting back product: '.$e->getMessage()));
			}
		}
		else{
			$this->messageManager->addError(__('Trash Id not found'));
		}
		$this->_redirect('*/product');
	}	
}
