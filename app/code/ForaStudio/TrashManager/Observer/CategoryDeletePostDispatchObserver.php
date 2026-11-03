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
use ForaStudio\TrashManager\Model\CategoryTrash;

class CategoryDeletePostDispatchObserver implements \Magento\Framework\Event\ObserverInterface
{
	/**
	 * @var \ForaStudio\TrashManager\Model\CategoryTrash
	 */
	protected $categoryTrashModel;

	/**
	 * @param \ForaStudio\TrashManager\Model\CategoryTrash 	  $categoryTrashModel
	 */
	public function __construct(
		\ForaStudio\TrashManager\Model\CategoryTrash $categoryTrashModel
	) {

		$this->categoryTrashModel = $categoryTrashModel;
	}

	/**
	 * @param \Magento\Framework\Event\Observer $observer
	 * @return void
	 */
	public function execute(\Magento\Framework\Event\Observer $observer)
    {   
    	$request = $observer->getRequest();
    	$id  = $request->getParam('id',false);
    	if($id && $this->categoryTrashModel->isCategoryExistInCatalog($id)){
    		$this->categoryTrashModel->load($id,'entity_id');
    		if($this->categoryTrashModel->getId()){
    			$this->categoryTrashModel->delete();
    		}
    	}
    	return $this;
    }
}	