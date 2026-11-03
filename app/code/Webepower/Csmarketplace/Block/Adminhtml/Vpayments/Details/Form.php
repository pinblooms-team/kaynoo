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

namespace Webepower\Csmarketplace\Block\Adminhtml\Vpayments\Details;
use Magento\Framework\Pricing\PriceCurrencyInterface;


class Form extends \Ced\CsMarketplace\Block\Adminhtml\Vpayments\Details\Form
{
    /**
     * @var \Ced\CsMarketplace\Helper\Acl
     */
    protected $_acl;
       /**
    * @var PriceCurrencyInterface
    */
    private $priceCurrency;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Locale\Currency
     */
    protected $_localeCurrency;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $orderHelper;

    /**
     * @var \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Vendorname
     */
    protected $vendorname;

    /**
     * Form constructor.
     * @param \Ced\CsOrder\Helper\Data $orderHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Locale\Currency $localeCurrency
     * @param \Ced\CsMarketplace\Helper\Acl $acl
     * @param \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Vendorname $vendorname
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vsettings\CollectionFactory $vsettingfactory
     * @param array $data
     */
    public function __construct(
        \Ced\CsOrder\Helper\Data $orderHelper,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Locale\Currency $localeCurrency,
        \Ced\CsMarketplace\Helper\Acl $acl,
        \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Vendorname $vendorname,
        \Ced\CsMarketplace\Model\ResourceModel\Vsettings\CollectionFactory $vsettingfactory,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->orderHelper = $orderHelper;
        $this->priceCurrency = $priceCurrency;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeCurrency,
            $acl,
            $vendorname,
            $vsettingfactory,
            $data
        );
    }

    /**
     * @return Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        if ($this->orderHelper->isActive()) {
            list($model, $fieldsets) = $this->loadFields();
            $form = $this->_formFactory->create();
            foreach ($fieldsets as $key => $data) {
                $fieldset = $form->addFieldset($key, ['legend' => $data['legend']]);
                foreach ($data['fields'] as $id => $info) {
                    if ($info['type'] == 'link') {
                        $fieldset->addField($id, $info['type'], [
                            'name' => $id,
                            'label' => $info['label'],
                            'href' => $info['href'],
                            'title' => $info['label'],
                            'value' => isset($info['value']) ? $info['value'] : $model->getData($id),
                            'after_element_html' => isset($info['after_element_html']) ?
                                $info['after_element_html'] : '',
                        ]);
                    } else {
                        $fieldset->addField($id, $info['type'], [
                            'name' => $id,
                            'title' => $info['label'],
                            'label' => $info['label'],
                            'value' => isset($info['value']) ? $info['value'] : $model->getData($id),
                            'text' => isset($info['text']) ? $info['text'] : $model->getData($id),
                            'after_element_html' => isset($info['after_element_html']) ?
                                $info['after_element_html'] : '',
                        ]);
                    }
                }
            }
            $this->setForm($form);
            return parent::_prepareForm();
        } else {
            return parent::_prepareForm();
        }
    }

    /**
     * Load fields
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Currency_Exception
     */
    protected function loadFields()
    {
        if ($this->orderHelper->isActive()) {
            $model = $this->_coreRegistry->registry('csmarketplace_current_transaction');
            $renderOrderDesc = $this->getLayout()
                ->createBlock(\Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid\Renderer\Orderdesc::class);

            $renderName = $this->vendorname;

            $vendorSettings = $this->_vsettingsFactory->create()->addFieldToFilter('vendor_id', $model->getData('vendor_id'));
            $data = [];
            foreach ($vendorSettings as $vendorSetting) {
                if ($model->getData('payment_code') == 'banktransfer') {
                    if ($vendorSetting['key'] == 'payment/vbanktransfer/bank_name')
                        $data['bank'] = $vendorSetting['value'];
                    if ($vendorSetting['key'] == 'payment/vbanktransfer/bank_branch_number')
                        $data['branch'] = $vendorSetting['value'];
                    if ($vendorSetting['key'] == 'payment/vbanktransfer/bank_swift_code')
                        $data['ifsc'] = $vendorSetting['value'];
                    if ($vendorSetting['key'] == 'payment/vbanktransfer/bank_account_number')
                        $data['account'] = $vendorSetting['value'];
                    if ($vendorSetting['key'] == 'payment/vbanktransfer/bank_account_name')
                        $data['holder'] = $vendorSetting['value'];
                }
                if ($model->getData('payment_code') == 'cheque') {
                    if ($vendorSetting['key'] == 'payment/vcheque/cheque_payee_name')
                        $data['payee'] = $vendorSetting['value'];
                }
            }
            $other = false;
            if ($model->getData('payment_code') == 'banktransfer') {
                $detail = 'Holder : ' . $data['holder'] . '<br>' . 'Bank : ' . $data['bank'] . '<br>' . 'Branch : ' .
                    $data['branch'] . '<br>' . 'IFSC : ' . $data['ifsc'] . '<br>' . 'Account No : ' . $data['account'];
            } elseif ($model->getData('payment_code') == 'cheque') {
                $detail = $data['payee'];
            } else {
                $other = true;
                $detail = $model->getData('payment_code_other');
            }
            $currencySymbol = $this->priceCurrency->getCurrencySymbol();
            $fieldsets = [
                'beneficiary_details' => [
                    'fields' => [
                        'vendor_id' => [
                            'label' => __('Vendor Name'),
                            'text' => $renderName->render($model),
                            'type' => 'note'
                        ],
                        'payment_code' => [
                            'label' => __('Payment Method'),
                            'type' => 'label',
                            'value' => $model->getData('payment_code')
                        ],
                        'payment_detail' => [
                            'label' => __('Beneficiary Details'),
                            'type' => 'note',
                            'text' => $detail
                        ],
                    ],
                    'legend' => __('Beneficiary Details')
                ],

                'order_details' => [
                    'fields' => [
                        'amount_desc' => [
                            'label' => __('Order Details'),
                            'text' => $renderOrderDesc->render($model),
                            'type' => 'note',
                        ],
                    ],
                    'legend' => __('Order Details')
                ],
            ];

            if ($model->getBaseCurrency() != $model->getCurrency()) {
                $fieldsets ['payment_details'] = [
                        'fields' => [
                            'transaction_id' => [
                                'label' => __('Transaction ID#'),
                                'value' => $model->getData('transaction_id'),
                                'type' => 'label'
                            ],
                            'created_at' => [
                                'label' => __('Transaction Date'),
                                'type' => 'label',
                                'value' => $model->getData('created_at')
                            ],
                            'payment_method' => [
                                'label' => __('Transaction Mode'),
                                'type' => 'label',
                                'value' => $this->_acl->getDefaultPaymentTypeLabel($model->getData('payment_method'))
                            ],
                            'transaction_type' => [
                                'label' => __('Transaction Type'),
                                'type' => 'label',
                                'value' => ($model->getData('transaction_type') == 0) ? __('Credit Type') :
                                    __('Debit Type')
                            ],
                            'total_shipping_amount' => [
                                'label' => __('Total Shipping Amount'),
                                'value' => $this->_localeCurrency->getCurrency($currencySymbol)
                                    ->toCurrency($model->getData('total_shipping_amount')),
                                'type' => 'label',
                            ],
                            'base_amount' => [
                                'label' => __('Amount'),
                                'value' => $this->_localeCurrency->getCurrency($currencySymbol)
                                    ->toCurrency($model->getData('base_amount')),
                                'type' => 'label',
                            ],
                            'base_fee' => [
                                'label' => __('Adjustment Amount'),
                                'type' => 'label',
                                'value' => $this->_localeCurrency->getCurrency($model->getCurrency())
                                    ->toCurrency($model->getData('base_fee')),
                            ],
                            'base_net_amount' => [
                                'label' => __('Net Amount'),
                                'type' => 'label',
                                'value' => $this->_localeCurrency->getCurrency($model->getCurrency())
                                    ->toCurrency($model->getData('base_net_amount'))
                            ],
                            'notes' => [
                                'label' => __('Notes'),
                                'type' => 'label',
                                'value' => $model->getData('notes'),
                            ],
                        ],
                        'legend' => __('Transaction Details')
                ];
            } else {
                $fieldsets['payment_details'] = [
                        'fields' => [
                            'transaction_id' => [
                                'label' => __('Transaction ID#'),
                                'type' => 'label',
                                'value' => $model->getData('transaction_id')
                            ],
                            'created_at' => [
                                'label' => __('Transaction Date'),
                                'value' => $model->getData('created_at'),
                                'type' => 'label',
                            ],
                            'payment_method' => [
                                'label' => __('Transaction Mode'),
                                'value' => $this->_acl->getDefaultPaymentTypeLabel($model->getData('payment_method')),
                                'type' => 'label',
                            ],
                            'transaction_type' => [
                                'label' => __('Transaction Type'),
                                'value' => ($model->getData('transaction_type') == 0) ? __('Credit Type') :
                                    __('Debit Type'),
                                'type' => 'label',
                            ],
                            'total_shipping_amount' => [
                                'label' => __('Total Shipping Amount'),
                                'value' => $this->_localeCurrency->getCurrency($model->getCurrency())
                                    ->toCurrency($model->getData('total_shipping_amount')),
                                'type' => 'label',
                            ],
                            'base_amount' => [
                                'label' => __('Amount'),
                                'value' => $this->_localeCurrency->getCurrency($model->getCurrency())
                                    ->toCurrency($model->getData('base_amount')),
                                'type' => 'label',
                            ],
                            'base_fee' => [
                                'label' => __('Adjustment Amount'),
                                'value' => $this->_localeCurrency->getCurrency($model->getCurrency())
                                    ->toCurrency($model->getData('base_fee')),
                                'type' => 'label',
                            ],
                            'base_net_amount' => [
                                'label' => __('Net Amount'),
                                'value' => $this->_localeCurrency->getCurrency($model->getCurrency())
                                    ->toCurrency($model->getData('base_net_amount')),
                                'type' => 'label',
                            ],
                            'notes' => [
                                'label' => __('Notes'),
                                'value' => $model->getData('notes'),
                                'type' => 'label',
                            ],
                        ],
                        'legend' => __('Transaction Details')
                ];
            }
            return [$model, $fieldsets];
        } else {
            return parent::loadFields();
        }
    }
}
