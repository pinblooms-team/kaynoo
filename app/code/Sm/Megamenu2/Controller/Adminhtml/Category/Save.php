<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sm\Megamenu2\Controller\Adminhtml\Category;

class Save extends \Magento\Catalog\Controller\Adminhtml\Category\Save
{  

    public function imagePreprocessing($data)
    {
        if (empty($data['image'])) {
            unset($data['image']);
            $data['image']['delete'] = true;
        }
		
        if (empty($data['sm_menu_icon_img'])) {
            unset($data['sm_menu_icon_img']);
            $data['sm_menu_icon_img']['delete'] = true;
        }
		
        return $data;
    }
	
    protected function _filterCategoryPostData(array $rawData)
    {
        $data = $rawData;
        // @todo It is a workaround to prevent saving this data in category model and it has to be refactored in future
        if (isset($data['image']) && is_array($data['image'])) {
            if (!empty($data['image']['delete'])) {
                $data['image'] = null;
            } else {
                if (isset($data['image'][0]['name']) && isset($data['image'][0]['tmp_name'])) {
                    $data['image'] = $data['image'][0]['name'];
                } else {
                    unset($data['image']);
                }
            }
        }
		
        if (isset($data['sm_menu_icon_img']) && is_array($data['sm_menu_icon_img'])) {
            if (!empty($data['sm_menu_icon_img']['delete'])) {
                $data['sm_menu_icon_img'] = null;
            } else {
                if (isset($data['sm_menu_icon_img'][0]['name']) && isset($data['sm_menu_icon_img'][0]['tmp_name'])) {
                    $data['sm_menu_icon_img'] = $data['sm_menu_icon_img'][0]['name'];
                } else {
                    unset($data['sm_menu_icon_img']);
                }
            }
        }
		
        return $data;
    }
	

}
