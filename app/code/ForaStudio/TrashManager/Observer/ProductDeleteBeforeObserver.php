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

use Magento\Backend\Model\Auth\Session;
use ForaStudio\TrashManager\Model\ProductTrash;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link as GroupedLink;
use Magento\Catalog\Model\Product\Link as ProductLink;

class ProductDeleteBeforeObserver implements \Magento\Framework\Event\ObserverInterface {

	protected $productEavTables = [
		'catalog_product_entity_datetime',
		'catalog_product_entity_decimal',
		'catalog_product_entity_int',
		'catalog_product_entity_text',
		'catalog_product_entity_tier_price',
		'catalog_product_entity_varchar',
		'catalog_product_entity_media_gallery_value',
		'catalog_product_entity_media_gallery_value_to_entity',
		'catalog_category_product',
		'catalog_product_website',
		'cataloginventory_stock_item',
		'cataloginventory_stock_status'
	];

	/**
	 * @var \ForaStudio\TrashManager\Model\ProductTrashFactory
	 */
	protected $productTrashFactory;

	/**
	 * @var \Magento\Framework\Filesystem
	 */
	protected $fileSystem;

	/**
	 * @var \ForaStudio\TrashManager\Helper\Data
	 */
	protected $helper;


	/**
	 * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
	 */
	protected $attributeSet;

	/**
	 * @var \Magento\Framework\Message\ManagerInterface
	 */
	protected $messageManager;

	/**
	 * @var \Magento\Framework\App\Filesystem\DirectoryList
	 */
	protected $directoryList;

	/**
	 * @var \Magento\Framework\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var 
	 */
	protected $readConnection;

	/**
	 * @var \Magento\Store\Model\ResourceModel\Website\Collection
	 */
	protected $websites;

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
	* @param \ForaStudio\TrashManager\Model\ProductTrashFactory 	$productTrashFactory
	* @param \Magento\Framework\Filesystem 							$fileSystem
	* @param \ForaStudio\TrashManager\Helper\Data 					$helper
	* @param \Magento\Eav\Api\AttributeSetRepositoryInterface 		$attributeSet
	* @param \Magento\Framework\Message\ManagerInterface 			$messageManager
	* @param \Magento\Framework\App\Filesystem\DirectoryList 		$directoryList
	* @param \Magento\Framework\ObjectManagerInterface 				$objectManager
	* @param \Magento\Framework\App\ResourceConnection 				$resource
	* @param  \Magento\Store\Model\ResourceModel\Website\Collection $websites
	* @param \Magento\Backend\Model\Auth                         	$auth
	* @param \Magento\Framework\HTTP\Header       								$header
	* @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress             	$remoteAddress
	*/
	public function __construct(
		\ForaStudio\TrashManager\Model\ProductTrashFactory $productTrashFactory,
		\Magento\Framework\Filesystem $fileSystem,
		\ForaStudio\TrashManager\Helper\Data $helper,
		\Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\App\Filesystem\DirectoryList $directoryList,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Store\Model\ResourceModel\Website\Collection $websites,
		\Magento\Backend\Model\Auth $auth,
		\Magento\Framework\HTTP\Header $header,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
	) {

		$this->_filesystem = $fileSystem;
		$this->helper = $helper;
		$this->attributeSet = $attributeSet;
		$this->productTrashFactory = $productTrashFactory;
		$this->messageManager = $messageManager;
		$this->directoryList = $directoryList;
		$this->objectManager = $objectManager;
		$this->readConnection = $resource->getConnection();
		$this->websites = $websites;
		$this->_auth = $auth;
		$this->remoteAddress = $remoteAddress;
        $this->header = $header;
		$this->directoryWrite = $fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
	}

	/**
	 * prepare product data to save in trsha before delete
	 *
	 * @param  \Magento\Framework\Event\Observer $observer
	 * @return this
	 */
	public function execute(\Magento\Framework\Event\Observer $observer) 
	{
		if ($this->helper->isProductTrashEnabled())
		{
			try{
				$product = $observer->getEvent()->getProduct();
				if ($product->getId()) {
					$product = $product->load($product->getId());
					$productTrash = $this->productTrashFactory->create();
					$productTrash->setEntityId($product->getId());
					$productTrash->setAttributeSetId($product->getAttributeSetId());
					$productTrash->setTypeId($product->getTypeId());
					$productTrash->setSku($product->getSku());
					$productTrash->setHasOptions($product->getHasOptions());
					$productTrash->setRequiredOptions($product->getRequiredOptions());
					$productTrash->setCreatedAt($product->getCreatedAt());
					$productTrash->setUpdatedAt($product->getUpdatedAt());
					$productTrash->setUserId($this->getUserId());
					$productTrash->setRemoteIp($this->remoteAddress->getRemoteAddress());
                    $productTrash->setUserAgent($this->header->getHttpUserAgent());

					$data=[];
					$this->prepareEavData($product->getId(),$data);
					$this->getMediaGalleryData($product->getId(),$data);
					$this->getReviewData($product->getId(),$data);
					$this->prepareCustomizableOption($product->getId(),$data);
					$this->prepareSeoData($product->getId(),$data);
					$this->prepareInventorySourceData($product->getSku(),$data);

					//Prepare Link Data
					$this->prepareLinkData($product->getId(),$data,ProductLink::LINK_TYPE_RELATED);
					$this->prepareLinkData($product->getId(),$data,ProductLink::LINK_TYPE_UPSELL);
					$this->prepareLinkData($product->getId(),$data,ProductLink::LINK_TYPE_CROSSSELL);
					

					//Prepare Type data
					$this->prepareProductTypeData($product,$data);
					$this->prepareGridData($product,$data);

					$productTrash->setProductData(json_encode($data));
					$productTrash->save();
				}
   			} catch (\Exception $e) {
				$this->messageManager->addException($e, $e->getMessage());
			}
		}
	}	

	/**
	 * prepare EavData
	 *
	 * @param  int $productId
	 * @param  array $data
	 * @return this
	 */
	protected function prepareEavData($productId,&$data) {

		// to keep master table on top and avoid error of child data insertion first
		$data['catalog_product_entity_media_gallery']=[];
		
		foreach ($this->productEavTables as $index=>$eavTable) {
			$conditionToMatch = $index > 7 ? "product_id" : "entity_id";
			$sql = "SELECT * from $eavTable where {$conditionToMatch} = " . $productId;
			$result = $this->readConnection->fetchAll($sql);
			if ($result && count($result)) {
				$data[$eavTable] = $result;
			}
		}
	}

	/**
	 * prepare media gallery data
	 *
	 * @param  int $productId
	 * @param  array $data
	 * @return this
	 */
	protected function getMediaGalleryData($productId,&$data)
	{	
		$data['images']=[];

		$mediaPath = $this->directoryList->getPath(DirectoryList::MEDIA);
		if(isset($data['catalog_product_entity_media_gallery_value']) && count($data['catalog_product_entity_media_gallery_value']))
		{
			$data["catalog_product_entity_media_gallery_value_video"]=[];
			
			$fetchedData=[];
			foreach($data['catalog_product_entity_media_gallery_value'] as $galleryValue)
			{
				if(in_array($galleryValue['value_id'], $fetchedData))
				{
					continue;
				}
				$sql = "SELECT * from  catalog_product_entity_media_gallery where value_id = " . $galleryValue['value_id'];
				$result = $this->readConnection->fetchAll($sql);

				$fetchedData[]=$galleryValue['value_id'];
				if(is_array($result) && count($result))
				{
					
					$data["catalog_product_entity_media_gallery"][]=$result[0];
					foreach($result as $media)
					{
						if (file_exists($mediaPath . "/catalog/product" . $media['value'])) {
							$destination = $mediaPath . '/' . ProductTrash::ENTITY_MEDIA_PATH . $media['value'];
							$this->directoryWrite->copyFile($mediaPath ."/catalog/product"  . $media['value'], $destination);
							$data['images'][]=$media['value'];
						}
					}
				}
				$sql = "SELECT * from  catalog_product_entity_media_gallery_value_video where value_id = " . $galleryValue['value_id'];
				$result = $this->readConnection->fetchAll($sql);
				if(is_array($result) && count($result))
				{
					$data["catalog_product_entity_media_gallery_value_video"][]=$result;
				}
			}

			if(!count($data["catalog_product_entity_media_gallery_value_video"]))
			{
				unset($data["catalog_product_entity_media_gallery_value_video"]);
			}
		}

	}

	/**
	 * prepare review data
	 *
	 * @param  int $productId
	 * @param  array $data
	 * @return this
	 */
	protected function getReviewData($productId, &$data)
	{
		$reviews = $this->readConnection->fetchAll("SELECT * FROM `review` WHERE entity_pk_value=" . $productId);
		if ($reviews && count($reviews)) {
			$data['review'] = $reviews;
			foreach ($reviews as $review) {
				$result = $this->readConnection->fetchAll("SELECT * FROM `review_detail` WHERE review_id=" . $review['review_id']);
				if ($result && count($result)) {
					if(!isset($data['review_detail']))
					{
						$data['review_detail']=[];
					}
					$data['review_detail'] = $result;
				}
			}
		}
	
		$result = $this->readConnection->fetchAll("SELECT * FROM rating_option_vote  WHERE entity_pk_value=" . $productId);
		if ($result && count($result)) {
			$data['rating_option_vote'] = $result;
		}

		$result = $this->readConnection->fetchAll("SELECT * FROM rating_option_vote_aggregated  WHERE entity_pk_value=" . $productId);
		if ($result && count($result)) {
			$data['rating_option_vote_aggregated'] = $result;
		}

		$result = $this->readConnection->fetchAll("SELECT * FROM review_entity_summary  WHERE entity_pk_value=" . $productId);
		if ($result && count($result)) {
			$data['review_entity_summary'] = $result;
		}
	}

	/**
	 * prepare product type data
	 *
	 * @param  int $productId
	 * @param  array $data
	 * @return this
	 */
	protected function prepareProductTypeData($product,&$data){

		switch($product->getTypeId()){
			case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE : 
				$this->preapreConfigurableTypeData($product,$data); 
				break;
			case \Magento\Bundle\Model\Product\Type::TYPE_CODE: 
				$this->preapreBundleTypeData($product,$data); 
				break;
			case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE: 
				$this->prepareLinkData($product->getId(),$data,GroupedLink::LINK_TYPE_GROUPED);
				break;
			case \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE: 
				$this->preapreDownloadableTypeData($product,$data);
				break;
			default : break;
		}
	}

	/**
	 * prepare configurable product type data
	 *
	 * @param  int $productId
	 * @param  array $data
	 * @return this
	 */
	protected function preapreConfigurableTypeData($product,&$data)
	{
		//Super link data
		$result = $this->readConnection->fetchAll('SELECT * FROM `catalog_product_super_link` WHERE parent_id=' . $product->getId());
		if (is_array($result) && count($result)) {
			$data['catalog_product_super_link'] = $result;
		}

		//Super attribute data
		$result = $this->readConnection->fetchAll('SELECT * FROM `catalog_product_super_attribute` WHERE product_id=' . $product->getId());
		if (is_array($result) && count($result)) {
			$data['catalog_product_super_attribute'] = $result;
			$supperAttributes=[];
			foreach($result as $attribute)
			{
				$supperAttributes[]=$attribute['product_super_attribute_id'];
			}
			if(count($supperAttributes))
			{
				//super attriburte label data
				$result = $this->readConnection->fetchAll('SELECT * FROM `catalog_product_super_attribute_label` WHERE product_super_attribute_id in (' .implode(',',$supperAttributes) .')');
				if (is_array($result) && count($result)) {
					$data['catalog_product_super_attribute_label'] = $result;
				}
			}
		}
	}

	/**
	 * prepare bundle product type data
	 *
	 * @param  int $productId
	 * @param  array $data
	 * @return this
	 */
	protected function preapreBundleTypeData($product,&$data)
	{
		//bundle option data
		$result = $this->readConnection->fetchAll('SELECT * FROM `catalog_product_bundle_option` WHERE parent_id=' . $product->getId());
		if (is_array($result) && count($result)) {
			$data['catalog_product_bundle_option'] = $result;
		}

		//bundle option value data
		$result = $this->readConnection->fetchAll('SELECT * FROM `catalog_product_bundle_option_value` WHERE parent_product_id=' . $product->getId());
		if (is_array($result) && count($result)) {
			$data['catalog_product_bundle_option_value'] = $result;
		}

		//bundle selection data
		$result = $this->readConnection->fetchAll('SELECT * FROM `catalog_product_bundle_selection` WHERE parent_product_id=' . $product->getId());
		if (is_array($result) && count($result)) {
			$data['catalog_product_bundle_selection'] = $result;
		}

		//bundle selection price data
		$result = $this->readConnection->fetchAll('SELECT * FROM `catalog_product_bundle_selection_price` WHERE parent_product_id=' . $product->getId());
		if (is_array($result) && count($result)) {
			$data['catalog_product_bundle_selection_price'] = $result;
		}
	}

	/**
	 * prepare downloadable product type data
	 *
	 * @param  int $productId
	 * @param  array $data
	 * @return this
	 */
	protected function preapreDownloadableTypeData($product,&$data)
	{
		//Downloadable link data
		$result = $this->readConnection->fetchAll("SELECT * FROM `downloadable_link` product_id=" . $product->getId());

		if (is_array($result) && count($result)) {
			$data['downloadable_link'] = $result;
			foreach($result as $link)
			{
				$mediaPath = $this->directoryList->getPath(DirectoryList::MEDIA);
				if (file_exists($mediaPath . "/downloadable/files/links" . $link['link_file'])) {
					$destination = $mediaPath . '/' . ProductTrash::ENTITY_MEDIA_PATH . $link['link_file'];
					$this->directoryWrite->copyFile($mediaPath . "/downloadable/files/links" . $link['link_file'], $destination);
				}
		        
				if (file_exists($mediaPath . "/downloadable/files/samples" . $link['sample_file'])) {
					$destination = $mediaPath . '/' . ProductTrash::ENTITY_MEDIA_PATH . $link['sample_file'];
					$this->directoryWrite->copyFile($mediaPath . "/downloadable/files/samples" . $link['sample_file'], $destination);
				}
			}
		}

		
	}

	/**
	 * prepare product ink(crosssell, upsell, related) data
	 *
	 * @param  int $productId
	 * @param  array $data
	 * @return this
	 */
	protected function prepareLinkData($productId,&$data,$linkType)
	{
		//grouped link data
		$result = $this->readConnection->fetchAll(
			'SELECT * FROM `catalog_product_link` WHERE product_id=' . $productId . 
			' and link_type_id = '. $linkType);
		if (is_array($result) && count($result)) {
			if(!isset($data['catalog_product_link']))
			{
				$data['catalog_product_link']=[];
			}
			
			foreach($result as $link)
			{
				$data['catalog_product_link'][]=$link;
				$attributeDecimal = $this->readConnection->fetchAll('SELECT * FROM `catalog_product_link_attribute_decimal` WHERE link_id='.$link['link_id']);
				if (is_array($attributeDecimal) && count($attributeDecimal)) {
					if(!isset($data['catalog_product_link_attribute_decimal']))
					{
						$data['catalog_product_link_attribute_decimal']=[];
					}
					$data['catalog_product_link_attribute_decimal'] = array_merge($data['catalog_product_link_attribute_decimal'],$attributeDecimal);
				}

				$attributeInt = $this->readConnection->fetchAll('SELECT * FROM `catalog_product_link_attribute_int` WHERE link_id='.$link['link_id']);
				if (is_array($attributeInt) && count($attributeInt)) {
					if(!isset($data['catalog_product_link_attribute_int']))
					{
						$data['catalog_product_link_attribute_int']=[];
					}
					$data['catalog_product_link_attribute_int'] =  array_merge($data['catalog_product_link_attribute_int'],$attributeInt);
				}

				$attributeVarchar = $this->readConnection->fetchAll('SELECT * FROM `catalog_product_link_attribute_varchar` WHERE link_id='.$link['link_id']);
				if (is_array($attributeVarchar) && count($attributeVarchar)) {
					if(!isset($data['catalog_product_link_attribute_varchar']))
					{
						$data['catalog_product_link_attribute_varchar']=[];
					}
					$data['catalog_product_link_attribute_varchar'] =  array_merge($data['catalog_product_link_attribute_varchar'],$attributeVarchar);
				}
				
			}
		}
	}

	/**
	 * prepare inventory source data
	 *
	 * @param  string $sku
	 * @param  array $data
	 * @return this
	 */
	protected function prepareInventorySourceData($sku,&$data)
	{
		$result = $this->readConnection->fetchAll("SELECT * FROM inventory_source_item  WHERE sku='" . $sku."'");
		if ($result && count($result)) {
			$data['inventory_source_item'] = $result;
		}
	}


	/**
	 * prepare product SEO data
	 *
	 * @param  int $productId
	 * @param  array $data
	 * @return this
	 */
	protected function prepareSeoData($productId,&$data)
	{
		$result = $this->readConnection->fetchAll("SELECT * FROM `url_rewrite` WHERE entity_id=$productId and entity_type = 'product'");
		if (is_array($result) && count($result)) {
			$data['url_rewrite']=$result;
		}
	}
	
	/**
	 * prepare product custom options data
	 *
	 * @param  int $productId
	 * @param  array $data
	 * @return this
	 */
	protected function prepareCustomizableOption($productId,&$data)
	{
		//Customizable Option Data

		$optionCollection = $this->objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Option\Collection');
		$optionCollection->addFieldToFilter('product_id', array('eq' => $productId));
		$optionId = [];
		if ($optionCollection->count()) {
			foreach ($optionCollection as $option) {
				

				if(!isset($data['catalog_product_option']))
				{
					$data['catalog_product_option']=[];
				}
				$result =  $this->readConnection->fetchAll("SELECT * FROM `catalog_product_option` WHERE option_id=" . $option->getOptionId());
				if (is_array($result) && count($result)) {
					$data['catalog_product_option'] = array_merge($data['catalog_product_option'],$result);
					
				}


				if(!isset($data['catalog_product_option_title']))
				{
					$data['catalog_product_option_title']=[];
				}
				$result =  $this->readConnection->fetchAll("SELECT * FROM `catalog_product_option_title` WHERE option_id=" . $option->getOptionId());
				if (is_array($result) && count($result)) {
					$data['catalog_product_option_title'] = array_merge($data['catalog_product_option_title'],$result);
					
				}

				if(!isset($data['catalog_product_option_price']))
				{
					$data['catalog_product_option_price']=[];
				}
				$result = $this->readConnection->fetchAll("SELECT * FROM `catalog_product_option_price` WHERE option_id=" . $option->getOptionId());
				if (is_array($result) && count($result)) {
					$data['catalog_product_option_price'] =  array_merge($result,$data['catalog_product_option_price']);
				}

				if(!isset($data['catalog_product_option_type_value']))
				{
					$data['catalog_product_option_type_value']=[];
				}
				$optionTypeValues = $this->readConnection->fetchAll("SELECT * FROM `catalog_product_option_type_value` WHERE option_id=" . $option->getOptionId());
				if (is_array($optionTypeValues) && count($optionTypeValues)) {
					$data['catalog_product_option_type_value'] =  array_merge($optionTypeValues,$data['catalog_product_option_type_value']);
					foreach ($optionTypeValues as $optionTypeValue) {
						
						$typeValues = $this->readConnection->fetchAll("SELECT * FROM `catalog_product_option_type_title` WHERE option_type_id=" . $optionTypeValue['option_type_id']);
						if (is_array($typeValues) && count($typeValues)) {
							if(!isset($data['catalog_product_option_type_title']))
							{
								$data['catalog_product_option_type_title']=[];
							}
							$data['catalog_product_option_type_title'] =  array_merge($typeValues,$data['catalog_product_option_type_title']);
						}

						$typePrices = $this->readConnection->fetchAll("SELECT * FROM `catalog_product_option_type_price` WHERE option_type_id=" .  $optionTypeValue['option_type_id']);
						if (is_array($typePrices) && count($typePrices)) {
							if(!isset($data['catalog_product_option_type_price']))
							{
								$data['catalog_product_option_type_price']=[];
							}
							$data['catalog_product_option_type_price'] =  array_merge($typePrices,$data['catalog_product_option_type_price']);
						}
					}
				}
				
			}
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
	 * @param  object $product
	 * @param  array $data
	 * @return this
	 */
	protected function prepareGridData($product,&$data)
	{
		$data['name'] = $product->getName();
		$options = $this->websites->addFieldToFilter('website_id',['in'=>$product->getWebsiteIds()]);
		$data['websites'] = [];

		foreach($options as $website)
		{
			$data['websites'][]=$website->getName();
		}
		$data['websites']=implode(',',$data['websites']);
		$options=\Magento\Catalog\Model\Product\Attribute\Source\Status::getOptionArray();
		$data['status'] =  $options[$product->getStatus()]->__toString();
		$options=\Magento\Catalog\Model\Product\Visibility::getOptionArray();
		$data['visibility'] = $options[$product->getVisibility()];
		$data['visibility'] =$data['visibility']->__toString();
		$data['price'] = $product->getPrice();
		$data['deleted_by'] = $this->_auth->getUser()->getFirstName() . ' '. $this->_auth->getUser()->getLastName();
	}
}
