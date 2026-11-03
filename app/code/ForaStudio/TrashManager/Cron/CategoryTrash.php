<?php
/**
 * ForaStudio Private Limited.
 *
 * @category  ForaStudio
 * @package   ForaStudio_TrashManager
 * @author    ForaStudio
 * @copyright Copyright (c) 2018-2019 ForaStudio Private Limited (https://forastudio.com)
 */

namespace ForaStudio\TrashManager\Cron;

/**
 * @covers \ForaStudio\TrashManager\Cron\DeleteForaCategoryTrash
 */
class CategoryTrash {

	protected $_logger;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	protected $date;

	/**
	 * @var \ForaStudio\TrashManager\Model\ResourceModel\CategoryTrash\CollectionFactory
	 */
	protected $trashcategoryCollection;

	/**
	 * @var \ForaStudio\TrashManager\Model\ConfigModel
	 */
	private $configModel;

	/**
	 * Construct
	 * @param \Psr\Log\LoggerInterface                                                     $logger
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime                                  $date
	 * @param \ForaStudio\TrashManager\Model\ResourceModel\CategoryTrash\CollectionFactory $trashcategoryCollection
	 * @param \ForaStudio\TrashManager\Helper\Data 										   $helper
	 */
	public function __construct(
		\Psr\Log\LoggerInterface $logger,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\ForaStudio\TrashManager\Model\ResourceModel\CategoryTrash\CollectionFactory $trashcategoryCollection,
		\ForaStudio\TrashManager\Helper\Data $helper
	) {
		$this->_logger = $logger;
		$this->date = $date;
		$this->trashcategoryCollection = $trashcategoryCollection;
		$this->helper = $helper;
	}

	/**
	 * Method executed when cron runs in server
	 */
	public function execute() {
		$days = $this->helper->getCategoryTrashAutoCleanDays();
		$date = $this->date->gmtDate('Y-m-d', "-{$days} days");
		$collection = $this->trashcategoryCollection->create()
			->addFieldToFilter('trashed_at', ['to' => $date.' 00:00:00']);
		$collection->walk('delete');
		$this->_logger->debug('Running Cron from Category trash cleaner');
		return $this;
	}
}
