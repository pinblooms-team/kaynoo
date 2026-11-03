<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sm\Megamenu2\Model\Category;

/**
 * Class DataProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        return [
            'general' =>
                [
                    'parent',
                    'path',
                    'is_active',
                    'include_in_menu',
                    'name',
                ],
            'content' =>
                [
                    'image',
                    'description',
                    'landing_page',
                ],
            'display_settings' =>
                [
                    'display_mode',
                    'is_anchor',
                    'available_sort_by',
                    'use_config.available_sort_by',
                    'default_sort_by',
                    'use_config.default_sort_by',
                    'filter_price_range',
                    'use_config.filter_price_range',
                ],
            'search_engine_optimization' =>
                [
                    'url_key',
                    'url_key_create_redirect',
                    'use_default.url_key',
                    'url_key_group',
                    'meta_title',
                    'meta_keywords',
                    'meta_description',
                ],
            'assign_products' =>
                [
                ],
            'design' =>
                [
                    'custom_use_parent_settings',
                    'custom_apply_to_products',
                    'custom_design',
                    'page_layout',
                    'custom_layout_update',
                ],
            'schedule_design_update' =>
                [
                    'custom_design_from',
                    'custom_design_to',
                ],
            'sm-menu' =>
                [
                    'sm_menu_hide_item',
                    'sm_menu_type',
                    'sm_menu_static_width',
                    'sm_menu_cat_columns',
                    'sm_menu_float_type',
                    'sm_menu_cat_label',
                    'sm_menu_icon_img',
                    'sm_menu_font_icon',
                    'sm_menu_block_top_content',
                    'sm_menu_block_left_width',
                    'sm_menu_block_left_content',
                    'sm_menu_block_right_width',
                    'sm_menu_block_right_content',
                    'sm_menu_block_bottom_content',
                ],
            'category_view_optimization' =>
                [
                ],
            'category_permissions' =>
                [
                ],
        ];
    }
	
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $category = $this->getCurrentCategory();
        if ($category) {
            $categoryData = $category->getData();
            $categoryData = $this->addUseDefaultSettings($category, $categoryData);
            $categoryData = $this->addUseConfigSettings($categoryData);
            $categoryData = $this->filterFields($categoryData);
            if (isset($categoryData['image'])) {
                unset($categoryData['image']);
                $categoryData['image'][0]['name'] = $category->getData('image');
                $categoryData['image'][0]['url'] = $category->getImageUrl();
            }
			
            if (isset($categoryData['sm_menu_icon_img'])) {
                unset($categoryData['sm_menu_icon_img']);
                $categoryData['sm_menu_icon_img'][0]['name'] = $category->getData('sm_menu_icon_img');
                $categoryData['sm_menu_icon_img'][0]['url'] = $this->getImageUrl($category->getData('sm_menu_icon_img'));
            }
			
            $this->loadedData[$category->getId()] = $categoryData;
        }
        return $this->loadedData;
    }	
	
    public function getImageUrl($image)
    {
        $url = false;
		$storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Store\Model\StoreManagerInterface');
        if ($image) {
            if (is_string($image)) {
                $url = $storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . 'catalog/category/' . $image;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }	
	
}
