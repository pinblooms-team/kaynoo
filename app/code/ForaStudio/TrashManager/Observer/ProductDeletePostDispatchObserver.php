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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\Model\Auth\Session;
use ForaStudio\TrashManager\Model\ProductTrash;

class ProductDeletePostDispatchObserver implements \Magento\Framework\Event\ObserverInterface
{
	/**
	 * @var \ForaStudio\TrashManager\Model\ProductTrash
	 */
	protected $productTrashModel;

	/**
	 * @param \ForaStudio\TrashManager\Model\ProductTrash 	  $productTrashModel
	 */
	public function __construct(
		\ForaStudio\TrashManager\Model\ProductTrash $categoryTrashModel
	) {
		$this->productTrashModel = $productTrashModel;
	}

	/**
	 * @param \Magento\Framework\Event\Observer $observer
	 * @return void
	 */
	public function execute(\Magento\Framework\Event\Observer $observer)
    {   
    	$request = $observer->getRequest();
    	$id  = $request->getParam('id',false);
    	if($id && $this->productTrashModel->isProductExistInCatalog($id)){
    		$this->productTrashModel->load($id,'entity_id');
    		if($this->productTrashModel->getId()){
    			$this->productTrashModel->delete();
    		}
    	}
    	return $this;
    }
}	