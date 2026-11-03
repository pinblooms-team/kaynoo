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

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class PriceData
 */
class PriceData extends Column {
	
	/**
	 * @var StoreManagerInterface
	 */
	protected $storeManager;

	/**
	 * @var CurrencyInterface
	 */
	protected $localeCurrency;
	
	/**
	 * @param ContextInterface $context
	 * @param UiComponentFactory $uiComponentFactory
	 * @param StoreManagerInterface $storeManager
	 * @param CurrencyInterface $localeCurrency
	 * @param array $components
	 * @param array $data
	 */
	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		StoreManagerInterface $storeManager,
		CurrencyInterface $localeCurrency,
		array $components = [],
		array $data = []
	) {
		$this->storeManager = $storeManager;
		parent::__construct($context, $uiComponentFactory, $components, $data);
		$this->localeCurrency = $localeCurrency;
	}

	/**
	 * Prepare Data Source
	 *
	 * @param array $dataSource
	 * @return array
	 */
	public function prepareDataSource(array $dataSource) {
		if (isset($dataSource['data']['items'])) {
			$priceData = $this->getData('name');
			foreach ($dataSource['data']['items'] as &$item) {
				$json_data = json_decode($item['product_data'], true);
				$store = $this->storeManager->getStore();
	            $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());
				$item[$priceData] = $currency->toCurrency(sprintf("%f",$json_data['price']));
			}
		}

		return $dataSource;
	}
}