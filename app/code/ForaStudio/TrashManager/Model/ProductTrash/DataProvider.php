<?php
/**
 * ForaStudio Private Limited.
 *
 * @category  ForaStudio
 * @package   ForaStudio_TrashManager
 * @author    ForaStudio
 * @copyright Copyright (c) 2018-2019 ForaStudio Private Limited (https://forastudio.com)
 */
namespace ForaStudio\TrashManager\Model\ProductTrash;

use ForaStudio\TrashManager\Model\ResourceModel\ProductTrash\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider {

	protected $collection;

	protected $dataPersistor;

	protected $loadedData;

	private $storeManager;

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param string $primaryFieldName
	 * @param string $requestFieldName
	 * @param CollectionFactory $collectionFactory
	 * @param DataPersistorInterface $dataPersistor
	 * @param array $meta
	 * @param array $data
	 */
	public function __construct(
		$name,
		$primaryFieldName,
		$requestFieldName,
		CollectionFactory $collectionFactory,
		DataPersistorInterface $dataPersistor,
		StoreManagerInterface $storeManager,
		array $meta = [],
		array $data = []
	) {
		$this->collection = $collectionFactory->create();
		$this->dataPersistor = $dataPersistor;
		$this->storeManager = $storeManager;
		parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
	}

	/**
	 * Get data
	 *
	 * @return array
	 */
	public function getData() {
		if (isset($this->loadedData)) {
			return $this->loadedData;
		}
		$items = $this->collection->getItems();
		/** @var \Magento\Cms\Model\Block $block */
		foreach ($items as $trash) {

			$this->loadedData[$trash->getId()] = $trash->getData();

		}

		$data = $this->dataPersistor->get('trashmanager');
		if (!empty($data)) {
			// $jsonData1=json_decode($items);
			$trash = $this->collection->getNewEmptyItem();
			$trash->setData($data);

			$model = $objectManager->create('ForaStudio\TrashManager\Model\ProductTrash');

			$this->loadedData[$trash->getId()] = $trash->getData();
			$this->dataPersistor->clear('trashmanager');
		}

		return $this->loadedData;
	}

}