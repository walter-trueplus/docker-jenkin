<?php

namespace Magestore\Webpos\Api\Sales\Order;

interface ShipmentRepositoryInterface extends \Magento\Sales\Api\ShipmentRepositoryInterface {
    /**
     * @param int $order_id
     * @return bool
     */
    public function createShipmentByOrderId($order_id);
}