<?php
/**
 * ForaStudio Private Limited.
 *
 * @category  ForaStudio
 * @package   ForaStudio_TrashManager
 * @author    ForaStudio
 * @copyright Copyright (c) 2018-2019 ForaStudio Private Limited (https://forastudio.com)
 */

namespace ForaStudio\TrashManager\Ui\Component\Listing\Column\Product;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class TrashActions
 */
class TrashActions extends Column {
	/**
	 * Url path
	 */
	const URL_PATH_DELETE = 'trashmanager/product/delete';
	const URL_PATH_PUTBACK = 'trashmanager/product/putback';
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
					$title = $this->getEscaper()->escapeHtml($item['trash_id']);
					$item[$this->getData('name')] = [
						'putback' => [
							'href' => $this->urlBuilder->getUrl(
								static::URL_PATH_PUTBACK,
								[
									'trash_id' => $item['trash_id'],
								]
							),
							'label' => __('Putback Product'),
							'confirm' => [
								'title' => __('Putback %1', $title),
								'message' => __('Are you sure you want to restore this product?'),
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
								'message' => __('Are you sure you want to delete this product?'),
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
	 * @deprecated 101.0.7
	 */
	private function getEscaper() {
		if (!$this->escaper) {
			$this->escaper = ObjectManager::getInstance()->get(Escaper::class);
		}
		return $this->escaper;
	}
}