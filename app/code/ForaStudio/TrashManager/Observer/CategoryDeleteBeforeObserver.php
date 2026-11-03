<?php
/**
 * ForaStudio Private Limited.
 *
 * @category  ForaStudio
 * @package   ForaStudio_TrashManager
 * @author    ForaStudio
 * @copyright Copyright (c) 2018-2019 ForaStudio Private Limited (https://forastudio.com)
 */

namespace ForaStudio\TrashManager\Observer;

use ForaStudio\TrashManager\Model\CategoryTrash;
use Magento\Framework\App\Filesystem\DirectoryList;

class CategoryDeleteBeforeObserver implements \Magento\Framework\Event\ObserverInterface {

	protected $categoryEavTables = [
		'catalog_category_entity_datetime',
		'catalog_category_entity_decimal',
		'catalog_category_entity_int',
		'catalog_category_entity_text',
		'catalog_category_entity_varchar',
		'catalog_category_product',
	];
	/**
	 * @var \ForaStudio\TrashManager\Model\CategoryTrashFactory
	 */
	protected $categoryTrashFactory;

	/**
	 * @var \Magento\Framework\Filesystem\Directory\WriteInterface
	 */
	protected $directoryWrite;

	/**
	 * @var \Magento\Framework\App\Filesystem\DirectoryList
	 */
	protected $directoryList;

	/**
	 * @var \Magento\Catalog\Api\CategoryRepositoryInterface
	 */
	protected $categoryRepository;

	/**
	 * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
	 */
	protected $categoryCollectionFactory;

	/**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Backend\Model\Auth  
     */
    protected $_auth;

     /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $header;

     /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;

	/**
	 * Construct
	 * @param \ForaStudio\TrashManager\Model\CategoryTrashFactory 				$categoryTrashFactory
	 * @param \Magento\Framework\Filesystem                       				$fileSystem
	 * @param \Magento\Framework\App\Filesystem\DirectoryList     				$directoryList
	 * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute  				$attributeFactory
	 * @param \Magento\Backend\Model\Auth                         				$auth
	 * @param \Magento\Framework\ObjectManagerInterface           				$objectManager
	 * @param \Magento\Catalog\Api\CategoryRepositoryInterface    				$categoryRepository
	 * @param \Magento\Framework\App\ResourceConnection           				$resource
	 * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory 	$categoryCollectionFactory
	 * @param \ForaStudio\TrashManager\Helper\Data             					$helper
	 * @param \Magento\Framework\HTTP\Header       								$header
	 * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress             	$remoteAddress
	 */
	public function __construct(
		\ForaStudio\TrashManager\Model\CategoryTrashFactory $categoryTrashFactory,
		\Magento\Framework\Filesystem $fileSystem,
		\Magento\Framework\App\Filesystem\DirectoryList $directoryList,
		\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
		\Magento\Backend\Model\Auth $auth,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
		\ForaStudio\TrashManager\Helper\Data $helper,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\HTTP\Header $header,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
	) {
		$this->directoryList = $directoryList;
		$this->categoryTrashFactory = $categoryTrashFactory;
		$this->_attributeFactory = $attributeFactory;
		$this->_auth = $auth;
		$this->objectManager = $objectManager;
		$this->categoryRepository = $categoryRepository;
		$this->readConnection = $resource->getConnection();
		$this->directoryWrite = $fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
		$this->categoryCollectionFactory = $categoryCollectionFactory;
		$this->helper = $helper;
		$this->messageManager = $messageManager;
		$this->remoteAddress = $remoteAddress;
        $this->header = $header;
	}

	/**
	 * @param \Magento\Framework\Event\Observer $observer
	 * @return void
	 */
	public function execute(\Magento\Framework\Event\Observer $observer) {
		if ($this->helper->isCategoryTrashEnabled()) {
			try {
				$category = $observer->getCategory();

				if ($category->getId()) {
					$mediaPath = $this->directoryList->getPath(DirectoryList::MEDIA);
					$data = $this->prepareEavData($category->getId(), $category);
					$this->prepareSeoData($category->getId(),$data);

					$categoryTrash = $this->categoryTrashFactory->create();
					$categoryTrash->setEntityId($category->getId());
					$categoryTrash->setAttributeSetId($category->getAttributeSetId());
					$categoryTrash->setParentId($category->getParentId());
					$categoryTrash->setCreatedAt($category->getCreatedAt());
					$categoryTrash->setUpdatedAt($category->getUpdatedAt());
					$categoryTrash->setPath($category->getPath());
					$categoryTrash->setPosition($category->getPosition());
					$categoryTrash->setLevel($category->getLevel());
					$categoryTrash->setChildrenCount($category->getChildrenCount());
					$categoryTrash->setEntityId($category->getId());
					$categoryTrash->setRemoteIp($this->remoteAddress->getRemoteAddress());
                    $categoryTrash->setUserAgent($this->header->getHttpUserAgent());

					if ($category->getImage() && file_exists($mediaPath . '/' . "/catalog/category" . '/' . $category->getImage())) {
						$destination = $mediaPath . '/' . CategoryTrash::ENTITY_MEDIA_PATH . '/' . $category->getImage();
						$this->directoryWrite->copyFile($mediaPath . '/' . "/catalog/category" . '/' . $category->getImage(), $destination);
						$data['image'] = $category->getImage();
					}

					$this->prepareGridData($category,$data);
					$categoryTrash->setCategoryData(json_encode($data));
					$categoryTrash->setUserId($this->getUserId());
					$categoryTrash->save();
				}
			} catch (\Exception $e) {
				$this->messageManager->addException($e, $e->getMessage());
			}
		}
	}

	/**
	 * prepareEavData
	 * @param  int $categoryId
	 * @return array
	 */
	protected function prepareEavData($categoryId) {
		$data = [];
		foreach ($this->categoryEavTables as $eavTable) {
			$conditionToMatch = $eavTable == "catalog_category_product" ? "category_id" : "entity_id";
			$sql = "SELECT * from $eavTable where {$conditionToMatch} = " . $categoryId;
			$result = $this->readConnection->fetchAll($sql);
			if ($result &&  count($result)) {
				$data[$eavTable] = $result;
			}
		}
		return $data;
	}

	/**
	 * preparePath
	 * @param  $category
	 * @return string
	 */
	protected function preparePath($category, $categorylevels) {
		$parents = [];
		if ((int) $categorylevels < 1) {
			$categorylevels = 1;
		}
		$collection = $this->categoryCollectionFactory->create()
			->addPathsFilter($category->getPath() . '/')
			->addLevelFilter($category->getLevel() + $categorylevels);
		foreach ($collection as $key => $collectiondata) {
			$parentIds = explode('/', $collectiondata->getPath());
			foreach ($parentIds as $parentId) {
				if ($parentId != $category->getId()) {
					$parentcategory = $this->categoryRepository->get($parentId);
					$parents[] = $parentcategory->getName();
				}
			}

		}
		
		return $parents;
	}

	/**
	 * prepare category SEO data
	 *
	 * @param  int $productId
	 * @param  array $data
	 * @return this
	 */
	protected function prepareSeoData($categoryId,&$data)
	{
		$result = $this->readConnection->fetchAll("SELECT * FROM `url_rewrite` WHERE entity_id=$categoryId and entity_type = 'category'");
		if (is_array($result) && count($result)) {
			$data['url_rewrite']=$result;
		}
	}


	/**
	 * getUserId
	 * @return int
	 */
	protected function getUserId() {
		$user = $this->_auth->getUser();
		return $user->getId();
	}

	/**
	 * prepare data to display in trash grid
	 *
	 * @param  object $category
	 * @param  array $data
	 * @return this
	 */
	protected function prepareGridData($category,&$data)
	{
		$data['name'] = $category->getName();
		$data['path'] = $category->getPath();
		$data['include_in_menu'] = $category->getIncludeInMenu()?'Yes':'NO';
		$data['deleted_by'] = $this->_auth->getUser()->getFirstName() . ' '. $this->_auth->getUser()->getLastName();
		$data['is_active'] =  $category->getIsActive()?'Yes':'NO'; 
	}
}
