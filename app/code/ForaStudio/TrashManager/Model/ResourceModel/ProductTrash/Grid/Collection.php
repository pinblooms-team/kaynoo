<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace ForaStudio\TrashManager\Model\ResourceModel\ProductTrash\Grid;

use ForaStudio\TrashManager\Model\ResourceModel\ProductTrash\Collection as TrashCollection;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;

class Collection extends TrashCollection implements SearchResultInterface {
	/**
	 * @var AggregationInterface
	 */
	private $aggregations;

	/**
	 * @param ExtensibleDataInterface[] $items
	 * @return $this
	 */
	public function setItems(array $items = null) {
		// echo get_class($this);exit;
		return $this;
	}

	/**
	 * @return AggregationInterface
	 */
	public function getAggregations() {
		return $this->aggregations;
	}

	/**
	 * @param AggregationInterface $aggregations
	 * @return void
	 */
	public function setAggregations($aggregations) {
		$this->aggregations = $aggregations;
	}

	/**
	 * @return \Magento\Framework\Api\SearchCriteriaInterface|null
	 */
	public function getSearchCriteria() {
		return null;
	}

	/**
	 * @param SearchCriteriaInterface $searchCriteria
	 * @return $this
	 */
	public function setSearchCriteria(SearchCriteriaInterface $searchCriteria) {
		return $this;
	}

	/**
	 * @return int
	 */
	public function getTotalCount() {
		return $this->getSize();
	}

	/**
	 * @param int $totalCount
	 * @return $this
	 */
	public function setTotalCount($totalCount) {
		return $this;
	}
}