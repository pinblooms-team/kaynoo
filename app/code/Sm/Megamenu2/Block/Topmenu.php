<?php
namespace Sm\Megamenu2\Block;

class Topmenu extends \Magento\Framework\View\Element\Template
{

    protected $_categoryHelper;
    protected $_categoryFlatConfig;
    protected $_topMenu;
    protected $_categoryFactory;
    protected $_helper;
    protected $_filterProvider;
    protected $_blockFactory;
    protected $_megamenu2Config;
    protected $_storeManager;
	private $registry;
	protected $_request ;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Sm\Megamenu2\Helper\Data $helper,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Theme\Block\Html\Topmenu $topMenu,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Framework\Registry $registry
    ) {

        $this->_categoryHelper = $categoryHelper;
        $this->_categoryFlatConfig = $categoryFlatState;
        $this->_categoryFactory = $categoryFactory;
        $this->_topMenu = $topMenu;
        $this->_helper = $helper;
        $this->_filterProvider = $filterProvider;
        $this->_blockFactory = $blockFactory;
        $this->_storeManager = $context->getStoreManager();
		$this->registry = $registry;
        $this->_request = $context->getRequest();
        parent::__construct($context);
    }

    public function getCategoryHelper()
    {
        return $this->_categoryHelper;
    }

    public function getCategoryModel($id)
    {
        $_category = $this->_categoryFactory->create();
        $_category->load($id);
        
        return $_category;
    }
    
    public function getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        return $this->_topMenu->getHtml($outermostClass, $childrenWrapClass, $limit);
    }
    
    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
    }
    
    public function getChildCategories($category)
    {
        if ($this->_categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource()) {
            $subcategories = (array)$category->getChildrenNodes();
        } else {
            $subcategories = $category->getChildren();
        }
        
        return $subcategories;
    }
    
    public function getActiveChildCategories($category)
    {
        $children = [];
        if ($this->_categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource()) {
            $subcategories = (array)$category->getChildrenNodes();
        } else {
            $subcategories = $category->getChildren();
        }
        foreach($subcategories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }
            $children[] = $category;
        }
        return $children;
    }
    
    public function getBlockContent($content = '') {
        if(!$this->_filterProvider)
            return $content;
        return $this->_filterProvider->getBlockFilter()->filter(trim($content));
    }
    
    public function getSubmenuItemsHtml($children, $level = 1, $max_level = 0, $column_width=12, $menu_type = 'fullwidth', $columns = null)
    {
        $html = '';
        
        if(!$max_level || ($max_level && $max_level == 0) || ($max_level && $max_level > 0 && $max_level-1 >= $level)) {
            $column_class = "";
            if($level == 1 && $columns && ($menu_type == 'fullwidth' || $menu_type == 'staticwidth')) {
                $column_class = "col-sm-".$column_width." ";
                $column_class .= "mega-columns columns".$columns;
            }
            $html = '<ul class="subchildmenu '.$column_class.'">';
            foreach($children as $child) {
                $cat_model = $this->getCategoryModel($child->getId());
                
                $sm_menu_hide_item = $cat_model->getData('sm_menu_hide_item');
                
                if (!$sm_menu_hide_item) {
                    $sub_children = $this->getActiveChildCategories($child);
                    
                    $sm_menu_cat_label = $cat_model->getData('sm_menu_cat_label');
                    $sm_menu_icon_img = $cat_model->getData('sm_menu_icon_img');
                    $sm_menu_font_icon = $cat_model->getData('sm_menu_font_icon');

                    $item_class = 'level'.$level.' ';
                    if(count($sub_children) > 0)
                        $item_class .= 'parent ';
                    $html .= '<li class="ui-menu-item '.$item_class.'">';
                    if(count($sub_children) > 0) {
                        $html .= '<div class="open-children-toggle"></div>';
                    }
                    if($level == 1 && $sm_menu_icon_img) {
                        $html .= '<div class="menu-thumb-img"><a class="menu-thumb-link" href="'.$this->_categoryHelper->getCategoryUrl($child).'"><img src="' . $this->_helper->getBaseUrl().'catalog/category/' . $sm_menu_icon_img . '" alt="'.$child->getName().'"/></a></div>';
                    }
                    $html .= '<a href="'.$this->_categoryHelper->getCategoryUrl($child).'">';
                    if ($level > 1 && $sm_menu_icon_img)
                        $html .= '<img class="menu-thumb-icon" src="' . $this->_helper->getBaseUrl().'catalog/category/' . $sm_menu_icon_img . '" alt="'.$child->getName().'"/>';
                    elseif($sm_menu_font_icon)
                        $html .= '<em class="menu-thumb-icon '.$sm_menu_font_icon.'"></em>';
                    $html .= '<span>'.$child->getName();
                    if($sm_menu_cat_label && $this->_megamenu2Config['cat_labels'][$sm_menu_cat_label])
                        $html .= '<span class="cat-label cat-label-'.$sm_menu_cat_label.'">'.$this->_megamenu2Config['cat_labels'][$sm_menu_cat_label].'</span>';
                    $html .= '</span></a>';
                    if(count($sub_children) > 0) {
                        $html .= $this->getSubmenuItemsHtml($sub_children, $level+1, $max_level, $column_width, $menu_type);
                    }
                    $html .= '</li>';
                }
            }
            $html .= '</ul>';
        }
        
        return $html;
    }
    
    public function getMegamenu2Html()
    {
        $html = '';
        
        $categories = $this->getStoreCategories(true,false,true);
        $current_cate_id = 0;
		if($this->registry->registry('current_category'))
		{
			$current_cate_id = $this->registry->registry('current_category')->getId();
		}
		
        $this->_megamenu2Config = $this->_helper->getConfig('megamenu2');
        $max_level = $this->_megamenu2Config['general']['max_level'];
        foreach($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }
            
			$cate_id = $category->getId();
			$class_active = '';
			if($cate_id == $current_cate_id)
			{$class_active = ' active';}
			
            $cat_model = $this->getCategoryModel($category->getId());
            
            $sm_menu_hide_item = $cat_model->getData('sm_menu_hide_item');
            
            if(!$sm_menu_hide_item) {
                $children = $this->getActiveChildCategories($category);
                $sm_menu_cat_label = $cat_model->getData('sm_menu_cat_label');
                $sm_menu_icon_img = $cat_model->getData('sm_menu_icon_img');
                $sm_menu_font_icon = $cat_model->getData('sm_menu_font_icon');
                $sm_menu_cat_columns = $cat_model->getData('sm_menu_cat_columns');
                $sm_menu_float_type = $cat_model->getData('sm_menu_float_type');
                
                if(!$sm_menu_cat_columns){
                    $sm_menu_cat_columns = 4;
                }
                
                $menu_type = $cat_model->getData('sm_menu_type');
                if(!$menu_type)
                    $menu_type = $this->_megamenu2Config['general']['menu_type'];
                    
                $custom_style = '';
                if($menu_type=="staticwidth")
                    $custom_style = ' style="width: 500px;"';

                $sm_menu_static_width = $cat_model->getData('sm_menu_static_width');
                if($menu_type=="staticwidth" && $sm_menu_static_width)
                    $custom_style = ' style="width: '.$sm_menu_static_width.';"';
                    
                $item_class = 'level0 ';
                $item_class .= $menu_type.' ';
                
                $menu_top_content = $cat_model->getData('sm_menu_block_top_content');
                $menu_left_content = $cat_model->getData('sm_menu_block_left_content');
                $menu_left_width = $cat_model->getData('sm_menu_block_left_width');
                if(!$menu_left_content || !$menu_left_width)
                    $menu_left_width = 0;
                $menu_right_content = $cat_model->getData('sm_menu_block_right_content');
                $menu_right_width = $cat_model->getData('sm_menu_block_right_width');
                if(!$menu_right_content || !$menu_right_width)
                    $menu_right_width = 0;
                $menu_bottom_content = $cat_model->getData('sm_menu_block_bottom_content');
                if($sm_menu_float_type)
                    $sm_menu_float_type = 'fl-'.$sm_menu_float_type.' ';
                if(count($children) > 0 || (($menu_type=="fullwidth" || $menu_type=="staticwidth") && ($menu_top_content || $menu_left_content || $menu_right_content || $menu_bottom_content)))
                    $item_class .= 'parent ';
                $html .= '<li class="ui-menu-item '.$item_class.$sm_menu_float_type.$class_active.'">';
                if(count($children) > 0) {
                    $html .= '<div class="open-children-toggle"></div>';
                }
                $html .= '<a href="'.$this->_categoryHelper->getCategoryUrl($category).'" class="level-top">';
                if ($sm_menu_icon_img)
                    $html .= '<img class="menu-thumb-icon" src="' . $this->_helper->getBaseUrl().'catalog/category/' . $sm_menu_icon_img . '" alt="'.$category->getName().'"/>';
                elseif($sm_menu_font_icon)
                    $html .= '<em class="menu-thumb-icon '.$sm_menu_font_icon.'"></em>';
                $html .= '<span>'.$category->getName().'</span>';
                if($sm_menu_cat_label && $this->_megamenu2Config['cat_labels'][$sm_menu_cat_label])
                    $html .= '<span class="cat-label cat-label-'.$sm_menu_cat_label.'">'.$this->_megamenu2Config['cat_labels'][$sm_menu_cat_label].'</span>';
                $html .= '</a>';
                if(count($children) > 0 || (($menu_type=="fullwidth" || $menu_type=="staticwidth") && ($menu_top_content || $menu_left_content || $menu_right_content || $menu_bottom_content))) {
                    $html .= '<div class="level0 submenu"'.$custom_style.'>';
                    if(($menu_type=="fullwidth" || $menu_type=="staticwidth") && $menu_top_content) {
                        $html .= '<div class="menu-top-block">'.$this->getBlockContent($menu_top_content).'</div>';
                    }
                    if(count($children) > 0 || (($menu_type=="fullwidth" || $menu_type=="staticwidth") && ($menu_left_content || $menu_right_content))) {
                        $html .= '<div class="row">';
                        if(($menu_type=="fullwidth" || $menu_type=="staticwidth") && $menu_left_content && $menu_left_width > 0) {
                            $html .= '<div class="menu-left-block col-sm-'.$menu_left_width.'">'.$this->getBlockContent($menu_left_content).'</div>';
                        }
                        $html .= $this->getSubmenuItemsHtml($children, 1, $max_level, 12-$menu_left_width-$menu_right_width, $menu_type, $sm_menu_cat_columns);
                        if(($menu_type=="fullwidth" || $menu_type=="staticwidth") && $menu_right_content && $menu_right_width > 0) {
                            $html .= '<div class="menu-right-block col-sm-'.$menu_right_width.'">'.$this->getBlockContent($menu_right_content).'</div>';
                        }
                        $html .= '</div>';
                    }
                    if(($menu_type=="fullwidth" || $menu_type=="staticwidth") && $menu_bottom_content) {
                        $html .= '<div class="menu-bottom-block">'.$this->getBlockContent($menu_bottom_content).'</div>';
                    }
                    $html .= '</div>';
                }
                $html .= '</li>';
				
            }
        }
		$display_other_menu = (int) $this->_megamenu2Config['other_menu']['display_other_menu'];
		$other_menu_title = $this->_megamenu2Config['other_menu']['other_menu_title'];
		$other_menu_link =  $this->_megamenu2Config['other_menu']['other_menu_link'];
		if ($display_other_menu && !empty($other_menu_title) && !empty($other_menu_link )){
			$url =  $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB).$other_menu_link;
			$active  =  $other_menu_link != '#' && strpos($this->_request->getFullActionName(), $other_menu_link) !== false ? ' active ' : '';
			$html .= '<li class="ui-menu-item level0 fullwidth '.$active.'"><a href="'.$url.'" class="level-top"><span>'.__($other_menu_title).'</span></a></li>';
		}
       
        return $html;
    }
}
