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
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 */
class Index extends \Magento\Backend\App\Action {

	/**
	 * Authorization level of a basic admin session
	 *
	 * @see _isAllowed()
	 */
	const ADMIN_RESOURCE = 'ForaStudio_TrashManager::product';

	/**
	 * @var PageFactory
	 */
	protected $resultPageFactory;
	protected $objectManager;

	/**
	 * @param Context $context
	 * @param PageFactory $resultPageFactory
	 */
	public function __construct(Context $context, PageFactory $resultPageFactory) {
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}

	/**
	 * Index action
	 *
	 * @return \Magento\Backend\Model\View\Result\Page
	 */
	public function execute() {

		$collectionFactory = $this->_objectManager->get(\Magento\Indexer\Model\Indexer\CollectionFactory::class);
		$indexers = $collectionFactory->create()->getItems();

		$indexers = array_combine(
			array_map(
				function ($item) {
					/** @var IndexerInterface $item */
					return $item->getId();
				},
				$indexers
			),
			$indexers
		);

		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('ForaStudio_TrashManager::foraStudio_trashmanager');
		$resultPage->addBreadcrumb(__('TrashManager'), __('TrashManager'));
		$resultPage->getConfig()->getTitle()->prepend(__('Trashed Products'));

		$dataPersistor = $this->_objectManager->get(\Magento\Framework\App\Request\DataPersistorInterface::class);
		$dataPersistor->clear('forastudio_trashmanager');

		return $resultPage;
	}

	/**
	 * getIndexers
	 * @return String
	 */
	protected function getIndexers() {
		$collectionFactory = $this->_objectManager->get(\Magento\Indexer\Model\Indexer\CollectionFactory::class);
		$indexers = $collectionFactory->create()->getItems();

		return array_combine(
			array_map(
				function ($item) {
					/** @var IndexerInterface $item */
					return $item->getId();
				},
				$indexers
			),
			$indexers
		);
	}
}