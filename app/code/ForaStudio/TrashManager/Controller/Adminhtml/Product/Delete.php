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

use Magento\Backend\App\Action;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Delete
 */
class Delete extends \Magento\Backend\App\Action {

	/**
	 * @var DateTime
	 */
	private $date;
	/**
	 * @var TimezoneInterface
	 */
	private $timezone;

	const ADMIN_RESOURCE = 'ForaStudio_TrashManager::product';
	
	/**
	 * Core registry
	 *
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry;

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $resultPageFactory;

	/**
	 * Construct
	 * @param Action\Context                             $context
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 * @param \Magento\Framework\Registry                $registry
	 * @param \ForaStudio\TrashManager\Helper\Data       $helper
	 * @param DateTime                                   $date
	 * @param TimezoneInterface                          $timezone
	 */
	public function __construct(
		Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\Registry $registry,
		\ForaStudio\TrashManager\Helper\Data $helper,
		DateTime $date,
		TimezoneInterface $timezone
	) {
		$this->resultPageFactory = $resultPageFactory;
		$this->_coreRegistry = $registry;
		parent::__construct($context);
		$this->helper = $helper;
		$this->date = $date;
		$this->timezone = $timezone;
	}

	/**
	 * Delete newsletter Template
	 *
	 * @return void
	 */
	public function execute() {
		$model = $this->_objectManager->create(\ForaStudio\TrashManager\Model\ProductTrash::class)->load(
			$this->getRequest()->getParam('trash_id'));
		if ($model->getId()) {
			try {
				$model->delete();
				$this->messageManager->addSuccess(__('The Product has been deleted.'));
				$this->_getSession()->setFormData(false);
			} catch (\Magento\Framework\Exception\LocalizedException $e) {
				$this->messageManager->addError($e->getMessage());
			} catch (\Exception $e) {
				$this->messageManager->addException($e, __('We can\'t delete this product right now.'));
			}
		}
		$this->_redirect('*/product');
	}
}