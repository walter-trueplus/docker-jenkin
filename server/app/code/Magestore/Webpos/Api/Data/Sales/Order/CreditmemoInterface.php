<?php

namespace Magestore\Webpos\Api\Data\Sales\Order;

interface CreditmemoInterface extends \Magento\Sales\Api\Data\CreditmemoInterface {

//    const ENTITY_ID = 'entity_id';
//    const STORE_ID = 'store_id';
//    const ADJUSTMENT_POSITIVE = 'adjustment_positive';
//    const BASE_SHIPPING_TAX_AMOUNT = 'base_shipping_tax_amount';
//    const STORE_TO_ORDER_RATE = 'store_to_order_rate';
//    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
//    const BASE_TO_ORDER_RATE = 'base_to_order_rate';
//    const GRAND_TOTAL = 'grand_total';
//    const BASE_ADJUSTMENT_NEGATIVE = 'base_adjustment_negative';
//    const BASE_SUBTOTAL_INCL_TAX = 'base_subtotal_incl_tax';
//    const SHIPPING_AMOUNT = 'shipping_amount';
//    const SUBTOTAL_INCL_TAX = 'subtotal_incl_tax';
//    const ADJUSTMENT_NEGATIVE = 'adjustment_negative';
//    const BASE_SHIPPING_AMOUNT = 'base_shipping_amount';
//    const STORE_TO_BASE_RATE = 'store_to_base_rate';
//    const BASE_TO_GLOBAL_RATE = 'base_to_global_rate';
//    const BASE_ADJUSTMENT = 'base_adjustment';
//    const BASE_SUBTOTAL = 'base_subtotal';
//    const DISCOUNT_AMOUNT = 'discount_amount';
//    const SUBTOTAL = 'subtotal';
//    const ADJUSTMENT = 'adjustment';
//    const BASE_GRAND_TOTAL = 'base_grand_total';
//    const BASE_ADJUSTMENT_POSITIVE = 'base_adjustment_positive';
//    const BASE_TAX_AMOUNT = 'base_tax_amount';
//    const SHIPPING_TAX_AMOUNT = 'shipping_tax_amount';
//    const TAX_AMOUNT = 'tax_amount';
//    const ORDER_ID = 'order_id';
//    const EMAIL_SENT = 'email_sent';
//    const CREDITMEMO_STATUS = 'creditmemo_status';
//    const STATE = 'state';
//    const SHIPPING_ADDRESS_ID = 'shipping_address_id';
//    const BILLING_ADDRESS_ID = 'billing_address_id';
//    const INVOICE_ID = 'invoice_id';
//    const STORE_CURRENCY_CODE = 'store_currency_code';
//    const ORDER_CURRENCY_CODE = 'order_currency_code';
//    const BASE_CURRENCY_CODE = 'base_currency_code';
//    const GLOBAL_CURRENCY_CODE = 'global_currency_code';
//    const TRANSACTION_ID = 'transaction_id';
//    const INCREMENT_ID = 'increment_id';
//    const CREATED_AT = 'created_at';
//    const UPDATED_AT = 'updated_at';
//    const DISCOUNT_TAX_COMPENSATION_AMOUNT = 'discount_tax_compensation_amount';
//    const BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'base_discount_tax_compensation_amount';
//    const SHIPPING_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'shipping_discount_tax_compensation_amount';
//    const BASE_SHIPPING_DISCOUNT_TAX_COMPENSATION_AMNT = 'base_shipping_discount_tax_compensation_amnt';
//    const SHIPPING_INCL_TAX = 'shipping_incl_tax';
//    const BASE_SHIPPING_INCL_TAX = 'base_shipping_incl_tax';
//    const DISCOUNT_DESCRIPTION = 'discount_description';
//    const ITEMS = 'items';
//    const COMMENTS = 'comments';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const POS_LOCATION_ID = 'pos_location_id';
    const REWARDPOINTS_DISCOUNT = 'rewardpoints_discount';
    const REWARDPOINTS_BASE_DISCOUNT = 'rewardpoints_base_discount';
    const REWARDPOINTS_EARN = 'rewardpoints_earn';
    const REFUND_EARNED_POINTS = 'refund_earned_points';
    const REFUND_POINTS = 'refund_points';
    const PAYMENTS = 'payments';
    /**
     * Get Payment
     *
     * @return \Magestore\Webpos\Api\Data\Checkout\Order\PaymentInterface[]|null
     */
    public function getPayments();
    /**
     * Set Payment
     *
     * @param \Magestore\Webpos\Api\Data\Checkout\Order\PaymentInterface[]|null $payment
     * @return CreditmemoInterface
     */
    public function setPayments($payment);
    /**
     * @return string|float
     */
    public function getRewardpointsDiscount();

    /**
     * @param string|float $rewardpoints_discount
     * @return CreditmemoInterface
     */
    public function setRewardpointsDiscount($rewardpoints_discount);
    /**
     * @return string|float
     */
    public function getRewardpointsBaseDiscount();

    /**
     * @param string|float $rewardpoints_base_discount
     * @return CreditmemoInterface
     */
    public function setRewardpointsBaseDiscount($rewardpoints_base_discount);
    /**
     * @return string|float
     */
    public function getRewardpointsEarn();

    /**
     * @param string|float $rewardpoints_earn
     * @return CreditmemoInterface
     */
    public function setRewardpointsEarn($rewardpoints_earn);
    /**
     * @return string|float
     */
    public function getRefundEarnedPoints();

    /**
     * @param string|float $refund_earned_points
     * @return CreditmemoInterface
     */
    public function setRefundEarnedPoints($refund_earned_points);
    /**
     * @return string|float
     */
    public function getRefundPoints();

    /**
     * @param string|float $refund_points
     * @return CreditmemoInterface
     */
    public function setRefundPoints($refund_points);
    /**
     * Gets credit memo items.
     *
     * @return \Magestore\Webpos\Api\Data\Sales\Order\Creditmemo\ItemInterface[] Array of credit memo items.
     */
    public function getItems();

    /**
     * Sets credit memo items.
     *
     * @param \Magestore\Webpos\Api\Data\Sales\Order\Creditmemo\ItemInterface[] $items
     * @return CreditmemoInterface
     */
    public function setItems($items);

    /**
     * Get order increment id
     *
     * @return string|null
     */
    public function getOrderIncrementId();
    /**
     * Set order increment id
     *
     * @param string|null $orderIncrementId
     * @return CreditmemoInterface
     */
    public function setOrderIncrementId($orderIncrementId);

    /**
     * Get pos location id
     *
     * @return int|null
     */
    public function getPosLocationId();
    /**
     * Set pos location id
     *
     * @param int|null $posLocationId
     * @return CreditmemoInterface
     */
    public function setPosLocationId($posLocationId);


}