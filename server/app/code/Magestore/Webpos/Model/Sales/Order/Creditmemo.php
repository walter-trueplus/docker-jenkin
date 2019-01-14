<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Model\Sales\Order;
use Magestore\Webpos\Api\Data\Sales\Order\CreditmemoInterface;

/**
 * Class Creditmemo
 * @package Magestore\Webpos\Model\Sales\Order
 */
class Creditmemo extends \Magento\Sales\Model\Order\Creditmemo
    implements \Magestore\Webpos\Api\Data\Sales\Order\CreditmemoInterface
{

    protected function _construct()
    {
        $this->_init(\Magestore\Webpos\Model\ResourceModel\Sales\Order\Creditmemo::class);
    }

    /**
     * Get order increment id
     *
     * @return string|null
     */
    public function getOrderIncrementId()
    {
        return $this->getData(self::ORDER_INCREMENT_ID);
    }

    /**
     * Set order increment id
     *
     * @param string|null $orderIncrementId
     * @return $this
     */
    public function setOrderIncrementId($orderIncrementId)
    {
        return $this->setData(self::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    /**
     * @inheritdoc
     */
    public function getPosLocationId()
    {
        return $this->getData(self::POS_LOCATION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setPosLocationId($posLocationId)
    {
        return $this->setData(self::POS_LOCATION_ID, $posLocationId);
    }

    /**
     * Gets credit memo items.
     *
     * @return \Magestore\Webpos\Api\Data\Sales\Order\Creditmemo\ItemInterface[] Array of credit memo items.
     */
    public function getItems()
    {
        return parent::getItems();
    }

    /**
     * Sets credit memo items.
     *
     * @param \Magestore\Webpos\Api\Data\Sales\Order\Creditmemo\ItemInterface[] $items
     * @return $this
     */
    public function setItems($items)
    {
        return $this->setData(self::ITEMS, $items);
    }

    /**
     * @return mixed
     */
    public function getItemsCollection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $itemCollection = $objectManager
            ->get('\Magestore\Webpos\Model\ResourceModel\Sales\Order\Creditmemo\Item\CollectionFactory')
            ->create();
        $collection = $itemCollection->setCreditmemoFilter($this->getId());

        if ($this->getId()) {
            foreach ($collection as $item) {
                $item->setCreditmemo($this);
            }
        }
        return $collection;
    }

    public function getPayments() {
        return $this->getData(self::PAYMENTS);
    }

    public function setPayments($payment) {
        return $this->setData(self::PAYMENTS, $payment);
    }

    public function getRewardpointsDiscount()
    {
        return $this->getData(self::REWARDPOINTS_DISCOUNT);
    }

    public function setRewardpointsDiscount($rewardpoints_discount)
    {
        return $this->setData(self::REWARDPOINTS_DISCOUNT, $rewardpoints_discount);
    }

    public function getRewardpointsBaseDiscount()
    {
        return $this->getData(self::REWARDPOINTS_BASE_DISCOUNT);
    }

    public function setRewardpointsBaseDiscount($rewardpoints_base_discount)
    {
        return $this->setData(self::REWARDPOINTS_BASE_DISCOUNT, $rewardpoints_base_discount);
    }

    public function getRewardpointsEarn()
    {
        return $this->getData(self::REWARDPOINTS_EARN);
    }

    public function setRewardpointsEarn($rewardpoints_earn)
    {
        return $this->setData(self::REWARDPOINTS_EARN, $rewardpoints_earn);
    }

    public function getRefundEarnedPoints()
    {
        return $this->getData(self::REFUND_EARNED_POINTS);
    }

    public function setRefundEarnedPoints($refund_earned_points)
    {
        return $this->setData(self::REFUND_EARNED_POINTS, $refund_earned_points);
    }

    public function getRefundPoints()
    {
        return $this->getData(self::REFUND_POINTS);
    }

    public function setRefundPoints($refund_points)
    {
        return $this->setData(self::REFUND_POINTS, $refund_points);
    }


}