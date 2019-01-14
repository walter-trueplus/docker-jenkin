<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Model\ResourceModel\Sales\Order;

use \Magestore\Webpos\Api\Data\Checkout\OrderInterface;

/**
 * Class Collection
 * @package Magestore\Webpos\Model\ResourceModel\Sales\Order
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magestore\Webpos\Model\Checkout\Order::class,
            \Magento\Sales\Model\ResourceModel\Order::class
        );
    }

    /**
     * joint to creditmemo table
     */
    public function joinToGetRefundPoint()
    {
        $this->getSelect()
            ->joinLeft(array('creditmemo' => $this->getTable('sales_creditmemo')),
                'main_table.entity_id = creditmemo.order_id',
                array(
                    'creditmemo_rewardpoints_earn'            => 'SUM(creditmemo.rewardpoints_earn)',
                    'creditmemo_rewardpoints_discount'        => 'SUM(creditmemo.rewardpoints_discount)',
                    'creditmemo_rewardpoints_base_discount'   => 'SUM(creditmemo.rewardpoints_base_discount)',
                )
            )
        ;
        $this->getSelect()->group('main_table.entity_id');
    }

    /**
     * joint to sales_item and sales_address table
     */
    public function joinToGetSearchString($queryString) {
        $searchString = new \Zend_Db_Expr(
            "CONCAT(
                            GROUP_CONCAT(DISTINCT main_table.increment_id SEPARATOR ', '),
                            GROUP_CONCAT(DISTINCT order_item.sku SEPARATOR ', '),
                            GROUP_CONCAT(DISTINCT order_item.name SEPARATOR ', '),
                            GROUP_CONCAT(DISTINCT coalesce(order_address.telephone,'') SEPARATOR ', '),
                            GROUP_CONCAT(DISTINCT main_table.customer_email SEPARATOR ', '),
                            GROUP_CONCAT(DISTINCT order_address.firstname SEPARATOR ', '),
                            GROUP_CONCAT(DISTINCT coalesce(order_address.middlename,'') SEPARATOR ', '),
                            GROUP_CONCAT(DISTINCT order_address.lastname SEPARATOR ', '),
                            GROUP_CONCAT(IFNULL(webpos_order_payment.reference_number, '')  SEPARATOR ', ')
                            )");
        $this->getSelect()
            ->join(array('order_item' => $this->getTable('sales_order_item')),
                'main_table.entity_id = order_item.order_id',
                array('search_string' => $searchString))
            ->join(array('order_address' => $this->getTable('sales_order_address')),
                'main_table.entity_id = order_address.parent_id',
                array())
            ->joinLeft(array('webpos_order_payment' => $this->getTable('webpos_order_payment')),
                'main_table.entity_id = webpos_order_payment.order_id',
                array())
            ;
        $this->getSelect()->group('main_table.entity_id');
        $this->getSelect()->having($searchString.' like "' . $queryString .'"');
    }


    /**
     *
     * override cause need left join
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);

        $part = $this->getSelect()->getPart(\Magento\Framework\DB\Select::GROUP);
        if (!is_array($part) || !count($part)) {
            $countSelect->columns(new \Zend_Db_Expr('COUNT(*)'));
            return $countSelect;
        }

        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
        $group = $this->getSelect()->getPart(\Magento\Framework\DB\Select::GROUP);
        $countSelect->columns(new \Zend_Db_Expr(("COUNT(DISTINCT ".implode(", ", $group).")")));
        return $countSelect;
    }

}
