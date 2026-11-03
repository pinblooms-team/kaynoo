<?php
/**
 * ForaStudio Private Limited.
 *
 * @category  ForaStudio
 * @package   ForaStudio_TrashManager
 * @author    ForaStudio
 * @copyright Copyright (c) 2018-2019 ForaStudio Private Limited (https://forastudio.com)
 */

namespace ForaStudio\TrashManager\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Filesystem\DirectoryList;

class ProductTrash extends AbstractModel {

	/**
	 * Path in /pub/media directory
	 */
	const ENTITY_MEDIA_PATH = 'trashmanager/catalog/product';

	protected $requiredindexers = [
		'catalog_product_category',
		'catalogrule_product',
		'catalog_product_attribute',
		'catalogsearch_fulltext',
		'catalog_product_price',
		'catalogrule_product',
		'cataloginventory_stock',
	];
	
	/**
	 * @var \Magento\Framework\App\Filesystem\DirectoryList
	 */
	protected $directoryList;

	/**
	 * @var \Magento\Framework\Filesystem\Directory\WriteInterface
	 */
	protected $directoryWrite;

	/**
	 * @var Resource
	 */
	protected $resource;

	/**
	 * @var \Magento\Framework\DB\Adapter\AdapterInterface
	 */
	protected $connection;

	/**
	 * @var \Magento\Catalog\Model\Product
	 */
	protected $productModel;

	/**
	 * @var \Magento\Indexer\Model\Indexer\CollectionFactory
	 */
	protected $indexerFactory;

	/**
	 * @var \Magento\Framework\Message\ManagerInterface
	 */
	protected $messageManager;
	
	/**
	 * Construct
	 * @param \Magento\Framework\Model\Context                                     		$context
	 * @param \Magento\Framework\Registry                                          		$registry
	 * @param ResourceConnection 														$resourceConnection
	 * @param \Magento\Framework\Filesystem 											$fileSystem
	 * @param \Magento\Framework\App\Filesystem\DirectoryList 							$directoryList
	 * @param \Magento\Indexer\Model\Indexer\CollectionFactory 							$indexerFactory
	 * @param \Magento\Catalog\Model\Product 											$productModel
	 * @param \ForaStudio\TrashManager\Model\ResourceModel\ProductTrash|null            $resource
	 * @param \ForaStudio\TrashManager\Model\ResourceModel\ProductTrash\Collection|null $collection
	 * @param \Magento\Framework\Message\ManagerInterface 								$messageManager
	 * @param array                                                                		$data
	 */
	public function __construct(
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		ResourceConnection $resourceConnection,
		\Magento\Framework\Filesystem $fileSystem,
		\Magento\Framework\App\Filesystem\DirectoryList $directoryList,
		\Magento\Indexer\Model\Indexer\CollectionFactory $indexerFactory,
		\Magento\Catalog\Model\Product $productModel,
		\ForaStudio\TrashManager\Model\ResourceModel\ProductTrash $resource = null,
		\ForaStudio\TrashManager\Model\ResourceModel\ProductTrash\Collection $collection = null,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		array $data = []
	) {
		$this->connection = $resourceConnection->getConnection();
		$this->directoryList = $directoryList;
		$this->directoryWrite = $fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
		$this->indexerFactory = $indexerFactory;
		$this->productModel = $productModel;
		$this->messageManager = $messageManager;
		parent::__construct($context, $registry, $resource, $collection, $data);
	}

	/**
	 * Initialization
	 *
	 *@return void
	 */
	protected function _construct() {
		$this->_init(\ForaStudio\TrashManager\Model\ResourceModel\ProductTrash::class);
	}

	/**
	 * Main function to put category entity back to catalog category
	 *
	 * @return boolean
	 * @throws \Exception
	 */
	public function putBack()
	{
		$this->insertBackToCatalog($this);
		$productId = $this->getEntityId();
		$this->delete();

		// Do indexing for restored product
		$this->productModel->load($productId);
		if ($this->productModel->getId()) {
			$this->productModel->save();
			$indexers = $this->getIndexers();
			foreach ($indexers as $key => $indexer) {
				if (in_array($key, $this->requiredindexers)) {
					$indexer->reindexRow($this->productModel->getId());
				}
			}
		}
	}

	/**
	 * Insert all the data and insert into respective tables of Catalog Category module
	 * @param  Object $category_data
	 * @return boolean
	 */
	protected function insertBackToCatalog($productTrash) {
		$entity_data = array(
			'entity_id' => $productTrash->getEntityId(),
			'attribute_set_id' => $productTrash->getAttributeSetId(),
			'type_id' => $productTrash->getTypeId(),
			'sku' => $productTrash->getSku(),
			'has_options' => $productTrash->getHasOptions(),
			'required_options' => $productTrash->getRequiredOptions(),
			'created_at' => $productTrash->getCreatedAt(),
			'updated_at' => $productTrash->getUpdatedAt(),
		);
		if ($this->connection->insert('catalog_product_entity', $entity_data)) {
			$this->insertEntityTableData($productTrash->getProductData());
		}
	}

	/**
	 * insertEntityTableData insert all the entity table data
	 * @param  JSON Object $productData product entity tables column and valued pair
	 * @return boolean
	 */
	protected function insertEntityTableData($productData) {
		$data = json_decode($productData, true);
		unset($data['name']);
		unset($data['websites']);
		unset($data['status']);
		unset($data['visibility']);
		unset($data['price']);
		unset($data['images']);
		unset($data['deleted_by']);
		foreach ($data as $key => $value) {
			if($key=='cataloginventory_stock_status'){
				continue;
			}
			try{
				if(count($value))
				{
					// As media table still consist row for deleted product 
					if($key=='catalog_product_entity_media_gallery'){
						$this->insertMediaRecord($value);
					}
					else{
						foreach($value as $tableData)
						{
							$this->connection->insertOnDuplicate($key,$tableData);
						}
						//$this->connection->insertMultiple($key, $value);	
					}
					
				}
			}
			catch(\Exception $e)
			{
				$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/trashmanager-putback.log');
				$logger = new \Zend\Log\Logger();
				$logger->addWriter($writer);
				$logger->info($e->getMessage());
				$logger->info($key);
				$logger->info(print_r($value,true));
				$this->messageManager->addException($e, $e->getMessage());
			}
			if($key == 'catalog_product_entity_media_gallery')
			{
				$this->moveMediaImages($value);
			}
			
		}
	}

	/**
	 * Checks the product exist in catalog
	 *
	 * @param int $entityId
	 * @return boolean
	 */
	public function isProductExistInCatalog($entityId)
	{
		$select = $this->connection->select()->from($this->connection->getTableName('catalog_product_entity'))
			->where(
				'entity_id = ?',
				$entityId
			);
		$data = $this->connection->fetchRow($select);
		if(is_array($data) && count($data) && $data['entity_id']==$entityId)
		{
			return true;
		}
		return false;
	}

	protected function moveMediaImages($media)
	{
		foreach ($media as $file) {
			$mediaPath = $this->directoryList->getPath(DirectoryList::MEDIA);
			$source = $mediaPath . '/' . self::ENTITY_MEDIA_PATH . $file['value'];
			$destination = $mediaPath . "/catalog/product" .$file['value'];
			if(!file_exists($destination) && file_exists($source)){
				$this->directoryWrite->copyFile($source, $destination);
			}
			if(file_exists($source))
			{
				unlink($source);	
			}
		}
	}

	/**
	 * getIndexers
	 * @return int
	 */
	protected function getIndexers() {
		$indexers = $this->indexerFactory->create()->getItems();
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

	/**
	 * Check record exists
	 * @param $valueId
	 * @return boolean
	 */
	protected function insertMediaRecord($medias)
	{
		$insertData=[];
		foreach($medias as $media)
		{
			$select = $this->connection->select()->from($this->connection->getTableName('catalog_product_entity_media_gallery'))
			->where(
				'value_id = ?',
				$media['value_id']
			);
			$data = $this->connection->fetchRow($select);
			if(!is_array($data) || !count($data) || $data['value_id']!=$media['value_id'])
			{
				$insertData[]=$media;
			}	
		}
		if(count($insertData))
		{
			$this->connection->insertMultiple('catalog_product_entity_media_gallery', $insertData);
		}
		
	}

}
