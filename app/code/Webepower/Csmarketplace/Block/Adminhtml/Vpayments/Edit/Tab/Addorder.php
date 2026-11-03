<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsTransaction
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Webepower\Csmarketplace\Block\Adminhtml\Vpayments\Edit\Tab;

use Ced\CsMarketplace\Model\Vpayment;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Addorder extends \Ced\CsMarketplace\Block\Adminhtml\Vpayments\Edit\Tab\Addorder
{
    /**
     * @var null
     */
    protected $_availableMethods = null;
       /**
    * @var PriceCurrencyInterface
    */
   private $priceCurrency;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    protected $_vendor;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_currencyInterface;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
     */
    protected $_vordersFactory;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders
     */
    protected $_vordersResource;

    /**
     * @var \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory
     */
    protected $_vtItemCollectionFactory;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory
     */
    protected $_resourceCollection;

    /**
     * @var \Ced\CsTransaction\Helper\Data
     */
    protected $helper;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $_csMarketplaceHelper;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $csorderHelper;

    /**
     * @var \Ced\CsTransaction\Block\Adminhtml\Vorder\Items\Grid\Renderer\Orderid
     */
    protected $cstransactionOrderid;

    /**
     * @var \Ced\CsMarketplace\Model\VpaymentFactory
     */
    protected $_vPaymentFactory;

    /**
     * Addorder constructor.
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $collectionFactory
     * @param \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory $vtItemCollectionFactory
     * @param \Ced\CsTransaction\Helper\Data $transactionHelper
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Ced\CsTransaction\Block\Adminhtml\Vorder\Items\Grid\Renderer\Orderid $cstransactionOrderid
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Ced\CsMarketplace\Model\Vendor $vendor
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders $vordersResource
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Backend\Model\Url $urlBuilder
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Orderid $orderid
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param Vpayment $vPaymentModel
     * @param \Ced\CsMarketplace\Model\VpaymentFactory $vPaymentFactory
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $collectionFactory,
        \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory $vtItemCollectionFactory,
        \Ced\CsTransaction\Helper\Data $transactionHelper,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Ced\CsTransaction\Block\Adminhtml\Vorder\Items\Grid\Renderer\Orderid $cstransactionOrderid,
        \Magento\Backend\Block\Template\Context $context,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Ced\CsMarketplace\Model\Vorders $vorders,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders $vordersResource,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Backend\Model\Url $urlBuilder,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Orderid $orderid,
        \Magento\Directory\Helper\Data $directoryHelper,
        Vpayment $vPaymentModel,
        \Ced\CsMarketplace\Model\VpaymentFactory $vPaymentFactory,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_vordersFactory = $vordersFactory;
        $this->_vordersResource = $vordersResource;
        $this->_resourceCollection = $collectionFactory;
        $this->_vtItemCollectionFactory = $vtItemCollectionFactory;
        $this->helper = $transactionHelper;
        $this->_csMarketplaceHelper = $csmarketplaceHelper;
        $this->csorderHelper = $csorderHelper;
        $this->cstransactionOrderid = $cstransactionOrderid;
        $this->_vPaymentFactory = $vPaymentFactory;
        $this->priceCurrency = $priceCurrency;
        parent::__construct(
            $vPaymentModel,
            $context,
            $vendor,
            $vorders,
            $localeCurrency,
            $urlBuilder,
            $formFactory,
            $orderid,
            $directoryHelper,
            $data
        );

        if ($this->csorderHelper->isActive()) {
            $this->setTemplate('Ced_CsTransaction::vpayments/edit/tab/addorder.phtml');
        }
    }

    /**
     * Round price
     * @param mixed $price
     * @return float
     */
    public function roundPrice($price)
    {
        return round($price??0.00, 2);
    }

    /**
     * Available Methods
     * @return array|null
     */
    public function availableMethods()
    {
        if ($this->_availableMethods == null) {
            $vendorId = $this->getRequest()->getParam('vendor_id', 0);
            $this->_availableMethods = $this->_vendor->getPaymentMethodsArray($vendorId);
        }
        return $this->_availableMethods;
    }

    /**
     * Prepare Layout
     * @return \Ced\CsMarketplace\Block\Adminhtml\Vpayments\Edit\Tab\Addorder|Addorder
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'csmarketplace_continue_button',
            $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
                ->setData([
                    'label' => __('Continue'),
                    'onclick' => "setSettings('" . $this->getContinueUrl() . "','vendor_id')",
                    'class' => 'save primary'
                ])
        );
        return parent::_prepareLayout();
    }

    /**
     * Get Countinue Url
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->_urlBuilder->getUrl('*/*/*', [
            '_current' => true,
            '_secure' => true,
            'vendor_id' => '{{vendor_id}}',
        ]);
    }

    /**
     * Get html for buttons
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonsHtml()
    {
        $addButtonData = [
            'label' => __('Add/Remove Amount(s) for Payment'),
            'onclick' => "this.parentNode.style.display = 'none';
            document.getElementById('order-search').style.display = ''",
            'class' => 'add',
        ];
        return $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->setData($addButtonData)->toHtml();
    }

    /**
     * Prepare html for notice block
     * @return string
     */
    protected function noticeBlock()
    {
        if (count($this->availableMethods()) == 0) {
            return '<div>
	              <ul class="messages">
	                  <li class="notice-msg">
	                      <ul>
	                          <li>' .
                __("Can't continue with payment,because vendor did not specify payment method(s).") . '</li>
	                      </ul>
	                  </li>
	              </ul>
	            </div>';
        }
        return '';
    }

    /**
     * Prepare html for search form
     * @return array|string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Currency_Exception
     */
    public function getSearchFormHtml()
    {
        if ($this->csorderHelper->isActive()) {
            $form = $this->_formFactory->create();
            $fieldset = $form->addFieldset('form_fields', ['legend' => __('Beneficiary  Information')]);
            $vendorId = $this->getRequest()->getParam('vendor_id', 0);
            $params = $this->getRequest()->getParams();
            $type = isset($params['type']) && in_array(
                $params['type'],
                array_keys($this->_vPaymentFactory->create()->getStates())
            ) ? $params['type'] : Vpayment::TRANSACTION_TYPE_CREDIT;

            $fieldset->addField('vendor_id', 'select', [
                'label' => __('Beneficiary Vendor'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'vendor_id',
                'script' => 'var cs_ok = 0;',
                'onchange' => "document.getElementById('order-items').innerHTML='';
                document.getElementById('order-search').innerHTML=''; setLocation('" .
                    $this->_urlBuilder->getUrl('*/*/*', ['type' => $type]) . "vendor_id/'+this.value);",
                'value' => $vendorId,
                'values' => $this->_resourceCollection->create()->toOptionpayArray(),
                'after_element_html' => '<small>Vendor selection will change the <b>"Selected Amount(s) for Payment"
                                        </b> section.</small>',
            ]);

            $params = $this->getRequest()->getParams();

            $type = isset($params['type']) && in_array(
                trim($params['type']),
                array_keys($this->_vPaymentFactory->create()->getStates())
            ) ? trim($params['type']) : Vpayment::TRANSACTION_TYPE_CREDIT;

            $relationIds = isset($params['order_ids']) ? explode(',', trim($params['order_ids'])) : [];
            $collection = $this->_vtItemCollectionFactory->create()->addFieldToFilter('vendor_id', ['eq' => $vendorId]);
            
            $main_table = $this->_csMarketplaceHelper->getTableKey('main_table');
            $item_fee = $this->_csMarketplaceHelper->getTableKey('item_fee');
            $amount_ready_to_refund = $this->_csMarketplaceHelper->getTableKey('amount_ready_to_refund');
            $qty_ready_to_refund = $this->_csMarketplaceHelper->getTableKey('qty_ready_to_refund');
            $item_commission = $this->_csMarketplaceHelper->getTableKey('item_commission');

            if ($type == Vpayment::TRANSACTION_TYPE_DEBIT) {
                $collection->addFieldToFilter('qty_ready_to_refund', ['gt' => 0]);
                $collection->getSelect()
                    ->columns(['net_vendor_earn' => new \Zend_Db_Expr("({$main_table}.{$amount_ready_to_refund})")]);
                $collection->getSelect()
                    ->columns(['commission_fee' => new \Zend_Db_Expr(
                        "({$main_table}.{$item_commission} * {$main_table}.{$qty_ready_to_refund})"
                    )
                    ]);
            } else {
                if (!$this->getRequest()->getParam('order_id_for_ship')) {
                    $collection->addFieldToFilter('qty_ready_to_pay', ['gt' => 0]);
                }

                $collection->getSelect()
                    ->columns(['net_vendor_earn' => new \Zend_Db_Expr("({$main_table}.{$item_fee})")]);
                $collection->getSelect()
                    ->columns(['commission_fee' => new \Zend_Db_Expr("({$main_table}.{$item_commission})")]);
            }
            
            $collection = $collection->addFieldToFilter('order_id', ['in' => $relationIds]);

            $renderer = $this->cstransactionOrderid;

            $html = "";
            $html .= '<div class="entry-edit">
						<div class="entry-edit-head">
							<div id="csmarketplace_add_more" style="float: right;">' . $this->getButtonsHtml() . '</div>
							<h4 class="icon-head head-cart">' . __("Selected Amount(s) for Payment") . '</h4>
						</div>
						<div class="grid" id="order-items_grid">
							<table cellspacing="0" class="data order-tables">
								<thead>
									<tr class="headings">
										<th class="no-link">' . __("Order ID") . '</th>';
            if ($type == Vpayment::TRANSACTION_TYPE_DEBIT) {
                $html .= '		            <th class="no-link">' . __("Returning Qty") . '</th>
											 <th class="no-link">' . __("Commission Fee") . '</th>
										<th class="no-link">' . __("Vendor Refund") . '</th>
										<th class="no-link">' . __("Include Shipping") . '</th>
									</tr>
								</thead>
								<tbody>
				';
            } else {
                $html .= '		            <th class="no-link">' . __("Paying Qty") . '</th>
											 <th class="no-link">' . __("Commission Fee") . '</th>
										<th class="no-link">' . ("Vendor Payment") . '</th>
										<th class="no-link">' . __("Include Shipping") . '</th>
									</tr>
								</thead>
								<tbody>
				';
            }
            $amount = 0.00;
            $shippingAmountPrice = 0.00;
            $class = '';
            $arrayShipping = [];

            foreach ($collection as $item) {
                $class = ($class == 'odd') ? 'even' : 'odd';
                $html .= '<tr class="' . $class . '"';
                $html .= '>';

                $html .= '<td><center>' . $renderer->render($item) . '</center></td>';
                if ($type == Vpayment::TRANSACTION_TYPE_DEBIT) {
                    $html .= '<td><center>' . $item->getQtyReadyToRefund() . '</center></td>';
                } else {
                    $html .= '<td><center>' . $item->getQtyReadyToPay() . '</center></td>';
                }

                $html .= '<td><center>' . $this->_currencyInterface
                            ->getCurrency($item->getBaseCurrency())
                            ->toCurrency($item->getCommissionFee()) . '</center></td>';
                $html .= '<td><center>' . $this->_currencyInterface
                            ->getCurrency($item->getBaseCurrency())
                            ->toCurrency($item->getNetVendorEarn());

                $amount += $item->getNetVendorEarn();

                $html .= '<input id="csmarketplace_vendor_orders_' . $item->getId() . '" type="hidden" value="' .
                        $this->roundPrice($this->roundPrice($item->getNetVendorEarn() +
                            $item->getCommissionFee())) .
                        '" name="orders[' . $item->getOrderId() . '][' . $item->getId() . ']"/>';

                $html .= '<input id="csmarketplace_vendor_commission_' . $item->getId() . '" type="hidden" value="' .
                        $this->roundPrice($item->getCommissionFee()) . '" name="comissionfee[' . $item->getOrderId() .
                        '][' . $item->getId() . ']"/>';

                $html .= '<input id ="marketplace_item_id" type="hidden" value="' . $item->getId() .
                        '" name="order_item_id[]">';

                $html .= '</center></td>';
                if (isset($arrayShipping[$item->getParentId()])) {
                    $html .= '<td><center></center></td>';
                } else {
                    $vorder = $this->_vordersFactory->create();
                    $this->_vordersResource->load($vorder, $item->getParentId());
                    $shippingAmount = $this->helper->getAvailableShipping($vorder, $type);

                    $shippingAmountPrice += $shippingAmount;//added

                    if ((float)$shippingAmount != 0) {
                        $arrayShipping[$item->getParentId()] = $item->getParentId();
                        $html .= '<td><input onclick="chooseShippingAmount(this,' . $item->getParentId() .
                                ');" type="checkbox" checked="checked" name="shippingcheck[' . $item->getParentId() .
                                ']" value="1"><input type="text" readonly="true"
                                class="validate-number-range number-range-0-' .
                                $shippingAmount . '" name="shippings[' . $item->getParentId() . ']" value="' .
                                $shippingAmount . '" id="shippings_' . $item->getParentId() . '"></td>';
                    } else {
                        $html .= '<td><center>' . $shippingAmount . '</center></td>';
                    }
                }
                $html .= '</tr>';
            }

            $html .= ' </tbody></table>
						   </div>
			</div>
			<script>
			function chooseShippingAmount(e, id){
				var amount = document.getElementById("csmarketplace_vendor_total").value;
					var shippAmount = document.getElementById("shippings_"+id).value;
				if(e.checked){
					document.getElementById("csmarketplace_vendor_total").value = (parseFloat(amount) +
					parseFloat(shippAmount)).toFixed(2);
				}
				else{
					document.getElementById("csmarketplace_vendor_total").value = (parseFloat(amount) -
					parseFloat(shippAmount)).toFixed(2);
				}


			}
			</script>
			';

            $amount += $shippingAmountPrice;

            $fieldset->addField('csmarketplace_vendor_total', 'text', [
                'label' => __('Total Amount'),
                'class' => 'required-entry validate-greater-than-zero',
                'required' => true,
                'name' => 'total',
                'value' => $this->roundPrice($amount),
                'readonly' => 'readonly',
            ]);

            return [$this->noticeBlock() . $form->toHtml(), $html];
        } else {
            return parent::getSearchFormHtml();
        }
    }

    /**
     * Prepare html for add order
     * @return string
     * @throws \Zend_Currency_Exception
     */
    public function getAddOrderBlock()
    {
        $main_table = $this->_csMarketplaceHelper->getTableKey('main_table');
        $item_fee = $this->_csMarketplaceHelper->getTableKey('item_fee');
        $qty_ready_to_refund = $this->_csMarketplaceHelper->getTableKey('qty_ready_to_refund');
        $item_commission = $this->_csMarketplaceHelper->getTableKey('item_commission');
        if ($this->csorderHelper->isActive()) {
            $params = $this->getRequest()->getParams();

            $relationIds = isset($params['order_ids']) ? $params['order_ids'] : [];

            $vendorId = $this->getRequest()->getParam('vendor_id', 0);
            $params = $this->getRequest()->getParams();
            $type = isset($params['type']) && in_array(
                $params['type'],
                array_keys($this->_vPaymentFactory->create()->getStates())
            ) ? $params['type'] : Vpayment::TRANSACTION_TYPE_CREDIT;
            $collection = $this->_vtItemCollectionFactory->create()
                ->addFieldToFilter('vendor_id', ['eq' => $vendorId]);

            if ($type == Vpayment::TRANSACTION_TYPE_DEBIT) {
                $collection->addFieldToFilter('qty_ready_to_refund', ['gt' => 0]);
                $collection->getSelect()
                    ->columns([
                        'net_vendor_earn' => new \Zend_Db_Expr(
                            "({$main_table}.{$item_fee} * {$main_table}.{$qty_ready_to_refund})"
                        )
                    ]);
                $collection->getSelect()
                    ->columns([
                        'commission_fee' => new \Zend_Db_Expr(
                            "({$main_table}.{$item_commission} * {$main_table}.{$qty_ready_to_refund})"
                        )
                    ]);
            } else {
                $collection->addFieldToFilter('qty_ready_to_pay', ['gt' => 0]);
                $collection->getSelect()
                    ->columns(['net_vendor_earn' => new \Zend_Db_Expr("({$main_table}.{$item_fee})")]);
                $collection->getSelect()
                    ->columns(['commission_fee' => new \Zend_Db_Expr("({$main_table}.{$item_commission})")]);
            }

            $collection = $collection->addFieldToFilter('id', ['in' => $relationIds]);

            $renderer = $this->cstransactionOrderid;

            $html = "";
            $html .= '<table cellspacing="0" class="data order-tables">
								<thead>
									<tr class="headings">
										<th class="no-link">' . __("Order ID") . '</th>';
            if ($type == Vpayment::TRANSACTION_TYPE_DEBIT) {
                $html .= '		            <th class="no-link">' . __("Returning Qty") . '</th>
											 <th class="no-link">' . __("Commission Fee") . '</th>
										<th class="no-link">' . __("Vendor Refund") . '</th>
										<th class="no-link">' . __("Include Shipping") . '</th>
									</tr>
								</thead>
								<tbody>
				';
            } else {
                $html .= '		            <th class="no-link">' . __("Paying Qty") . '</th>
											 <th class="no-link">' . __("Commission Fee") . '</th>
										<th class="no-link">' . __("Vendor Payment") . '</th>
										<th class="no-link">' . __("Include Shipping") . '</th>
									</tr>
								</thead>
								<tbody>
				';
            }

            $amount = 0.00;
            $shippingAmountPrice = 0.00;
            $class = '';
            $arrayShipping = [];
            $currencySymbol = $this->priceCurrency->getCurrencySymbol();
            foreach ($collection as $item) {
                if ($item->getQtyOrdered() == $item->getQtyReadyToPay() + $item->getQtyRefunded()) {
                    $class = ($class == 'odd') ? 'even' : 'odd';
                    $html .= '<tr class="' . $class . '"';
                    $html .= '>';
                    $html .= '<td><center>' . $renderer->render($item) . '</center></td>';
                    if ($type == Vpayment::TRANSACTION_TYPE_DEBIT) {
                        $html .= '<td><center>' . $item->getQtyReadyToRefund() . '</center></td>';
                    } else {
                        $html .= '<td><center>' . $item->getQtyReadyToPay() . '</center></td>';
                    }

                    $amount += $item->getNetVendorEarn();
                    $html .= '<input id="csmarketplace_vendor_orders_' . $item->getId() . '" type="hidden" value="' .
                        $this->roundPrice($this->roundPrice($item->getNetVendorEarn() +
                            $item->getCommissionFee())) .
                        '" name="orders[' . $item->getOrderId() . '][' . $item->getId() . ']"/>';
                    $html .= '<input id="csmarketplace_vendor_commission_' . $item->getId() .
                        '" type="hidden" value="' .
                        $this->roundPrice($item->getCommissionFee()) . '" name="comissionfee[' . $item->getOrderId() .
                        '][' . $item->getId() . ']"/>';
                    $html .= '<input id ="marketplace_item_id" type="hidden" value="' . $item->getId() .
                        '" name="order_item_id[]">';
                    $html .= '</center></td>';
                    $html .= '<td><center>' . $this->_currencyInterface
                            ->getCurrency($currencySymbol)
                            ->toCurrency($this->roundPrice($item->getCommissionFee())) . '</center></td>';
                    $html .= '<td><center>' . $this->_currencyInterface
                            ->getCurrency($currencySymbol)
                            ->toCurrency($this->roundPrice($this
                                ->roundPrice($item->getNetVendorEarn()))) . '</center></td>';

                    if (isset($arrayShipping[$item->getParentId()])) {
                        $html .= '<td><center></center></td>';
                    } else {
                        $vorder = $this->_vordersFactory->create();
                        $this->_vordersResource->load($vorder, $item->getParentId());
                        $shippingAmount = $this->helper->getAvailableShipping($vorder, $type);
                        $shippingAmountPrice += $shippingAmount;//added

                        if ((float)$shippingAmount != 0) {
                            $arrayShipping[$item->getParentId()] = $item->getParentId();
                            $html .= '<td><input  onclick="chooseShippingAmount(this,' . $item->getParentId() .
                                ');" type="checkbox"   checked="checked" name="shippingcheck[' . $item->getParentId() .
                                ']" value="1"><input type="text"  readonly="true"
                                 class="validate-number-range number-range-0-' .
                                $shippingAmount . '" name="shippings[' . $item->getParentId() . ']" value="' .
                                $shippingAmount .
                                '"  id="shippings_' . $item->getParentId() . '"></td>';
                        } else {
                            $html .= '<td><center>' . $shippingAmount . '</center></td>';
                        }
                    }
                    $html .= '</tr>';
                }
            }
            $amount += $shippingAmountPrice;
            $html .= '<input type="hidden" id="csmarketplace_fetched_total" value="' .
                $this->roundPrice($amount) . '"/></tbody></table>';
            return $html;
        } else {
            return parent::getAddOrderBlock();
        }
    }
}
