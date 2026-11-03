<?php
/**
 * ForaStudio Private Limited.
 *
 * @category  ForaStudio
 * @package   ForaStudio_TrashManager
 * @author    ForaStudio
 * @copyright Copyright (c) 2018-2019 ForaStudio Private Limited (https://forastudio.com)
 */
namespace ForaStudio\TrashManager\Ui\Component\Listing\Column\Category;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class TrashCategoryActions
 */
class TrashCategoryActions extends Column {
	/**
	 * Url path
	 */
	const URL_PATH_PUTBACK = 'trashmanager/category/putback';
	const URL_PATH_DELETE = 'trashmanager/category/delete';
	/**
	 * @var UrlInterface
	 */
	protected $urlBuilder;

	/**
	 * @var Escaper
	 */
	private $escaper;

	/**
	 * Constructor
	 *
	 * @param ContextInterface $context
	 * @param UiComponentFactory $uiComponentFactory
	 * @param UrlInterface $urlBuilder
	 * @param array $components
	 * @param array $data
	 */
	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		UrlInterface $urlBuilder,
		array $components = [],
		array $data = []
	) {
		$this->urlBuilder = $urlBuilder;
		parent::__construct($context, $uiComponentFactory, $components, $data);
	}

	/**
	 * Prepare Data Source
	 *
	 * @param array $dataSource
	 * @return array
	 */
	public function prepareDataSource(array $dataSource) {
		if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as &$item) {
				if (isset($item['trash_id'])) {
					$name = isset($item['name']) ? $item['name'] : "No Name";
					$title = $this->getEscaper()->escapeHtml($name);
					$item[$this->getData('name')] = [
						'putback' => [
							'href' => $this->urlBuilder->getUrl(
								static::URL_PATH_PUTBACK,
								[
									'trash_id' => $item['trash_id'],
								]
							),
							'label' => __('Put Back'),
							'confirm' => [
								'title' => __('Put back %1', $title),
								'message' => __('Are you sure you want to restore this category a %1 Category?', $title),
							],
						],
						'delete' => [
							'href' => $this->urlBuilder->getUrl(
								static::URL_PATH_DELETE,
								[
									'trash_id' => $item['trash_id'],
								]
							),
							'label' => __('Delete'),
							'confirm' => [
								'title' => __('Delete %1', $title),
								'message' => __('Are you sure you want to delete a %1 category?', $title),
							],
						],
					];
				}
			}
		}

		return $dataSource;
	}

	/**
	 * Get instance of escaper
	 * @return Escaper
	 */
	private function getEscaper() {
		if (!$this->escaper) {
			$this->escaper = ObjectManager::getInstance()->get(Escaper::class);
		}
		return $this->escaper;
	}
}