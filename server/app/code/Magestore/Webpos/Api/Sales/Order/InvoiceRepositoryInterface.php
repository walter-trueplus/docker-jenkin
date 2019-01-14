<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Api\Sales\Order;

interface InvoiceRepositoryInterface
{
    /**
     * @param int $orderId
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Exception
     */
    public function createInvoiceByOrderId($orderId);

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Exception
     */
    public function createInvoiceByOrder($order);
}
