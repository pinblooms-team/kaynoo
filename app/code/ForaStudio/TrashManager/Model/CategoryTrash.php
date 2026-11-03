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

class CategoryTrash extends AbstractModel {

	/**
	 * Path in /pub/media directory
	 */
	const ENTITY_MEDIA_PATH = 'trashmanager/catalog/category';

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
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

	/**
	 * Constructor
	 *
	 * @param \Magento\Framework\Model\Context                                  	 $context
	 * @param \Magento\Framework\Registry                                       	 $registry
	 * @param \ForaStudio\TrashManager\Model\ResourceModel\CategoryTrash             $resource
	 * @param \ForaStudio\TrashManager\Model\ResourceModel\CategoryTrash\Collection  $collection
	 * @param ResourceConnection                          							 $resource
	 * @param \Magento\Framework\Filesystem                       					 $fileSystem
	 * @param \Magento\Framework\App\Filesystem\DirectoryList     					 $directoryList
	 * @param \Magento\Framework\Message\ManagerInterface 							 $messageManager
	 * @param array                                                             	 $data
	 */
	public function __construct(
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\ForaStudio\TrashManager\Model\ResourceModel\CategoryTrash $resource,
		\ForaStudio\TrashManager\Model\ResourceModel\CategoryTrash\Collection $collection,
		ResourceConnection $resourceConnection,
		\Magento\Framework\Filesystem $fileSystem,
		\Magento\Framework\App\Filesystem\DirectoryList $directoryList,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		array $data = []
	) {

		$this->resource = $resourceConnection;
		$this->connection = $resourceConnection->getConnection();
		$this->directoryList = $directoryList;
		$this->directoryWrite = $fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
		$this->messageManager = $messageManager;
		parent::__construct($context, $registry, $resource, $collection, $data);
	}

	/**
	 * Main function to put category entity back to catalog category
	 *
	 * @return boolean
	 * @throws \Exception
	 */
	public function putBack()
	{
		if($this->isCategoryExistInCatalog($this->getParentId()))
		{
			$this->insertBackToCatalog($this);
			$this->delete();
		}
		else{
			throw new \Exception(__('Parent Category not exist in catalog category tree'));
		}
	}

	/**
	 * Checks the category exist in catalog category tree
	 *
	 * @param int $entityId
	 * @return boolean
	 */
	public function isCategoryExistInCatalog($entityId) {
		$select = $this->connection->select()->from($this->connection->getTableName('catalog_category_entity'))
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

	/**
	 * Insert all the data and insert into respective tables of Catalog Category module
	 * @param  Object $categoryTrash
	 * @return boolean
	 */
	protected function insertBackToCatalog($categoryTrash) {
		$entity_data = array(
			'entity_id' => $categoryTrash->getEntityId(),
			'attribute_set_id' => $categoryTrash->getAttributeSetId(),
			'parent_id' => $categoryTrash->getParentId(),
			'created_at' => $categoryTrash->getCreatedAt(),
			'updated_at' => $categoryTrash->getUpdatedAt(),
			'path' => $categoryTrash->getPath(),
			'position' => $categoryTrash->getPosition(),
			'level' => $categoryTrash->getLevel(),
			'children_count' => $categoryTrash->getChildrenCount(),
		);
		if ($this->connection->insert('catalog_category_entity', $entity_data)) {
			$this->insertEntityTableData($categoryTrash->getCategoryData());
		}
	}

	/**
	 * insertEntityTableData insert all the entity table data
	 * @param  JSON Object $cat_entity category entity table column and valued pair
	 * @return boolean
	 */
	protected function insertEntityTableData($cat_entity) {
		$cat_data = json_decode($cat_entity, true);
		unset($cat_data['name']);
		unset($cat_data['path']);
		unset($cat_data['include_in_menu']);
		unset($cat_data['deleted_by']);
		unset($cat_data['is_active']);
		
		if(isset($cat_data['image']))
		{
			$this->putBackCategoryImage($cat_data['image']);
		}
		unset($cat_data['image']);
		foreach ($cat_data as $key => $value) {
			try{
				if(count($value))
				{
					$this->connection->insertMultiple($key, $value);
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
		}
	}

	/**
	 * Move category image to its original directory
	 * @param string $image
	 * @return this
	 */
	protected function putBackCategoryImage($image)
	{
		$mediaPath = $this->directoryList->getPath(DirectoryList::MEDIA);
		$source = $mediaPath . '/' . self::ENTITY_MEDIA_PATH . '/' . $image;
		$destination = $mediaPath . "/catalog/category/" .$image;;
		if(!file_exists($destination) && file_exists($source)){
			$this->directoryWrite->copyFile($source, $destination);
		}
		if(file_exists($source)){
			unlink($source);	
		}
	}

	/**
	 * Delete category image upon deleting bin 
	 * @return this
	 */
	public function beforeDelete()
    {
    	$information = json_decode($this->getCategoryData(),true);
		if(isset($information['image']))
		{
		    $mediaPath = $this->directoryList->getPath(DirectoryList::MEDIA);
		    $targetPath = $mediaPath . '/catalog/version/category/' . $this->getCategoryId();
		    if(file_exists($source = $mediaPath . '/' . self::ENTITY_MEDIA_PATH . '/' . $information['image']))
		    {
		        unlink($source = $mediaPath . '/' . self::ENTITY_MEDIA_PATH . '/' . $information['image']);
		    }
		}
    }
}