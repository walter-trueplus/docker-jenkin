<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Api\Sales\Order;

interface CreditmemoRepositoryInterface
{
    /**
     * @param \Magestore\Webpos\Api\Data\Sales\Order\CreditmemoInterface $creditmemo
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createCreditmemoByOrderId($creditmemo);

    /**
     * @param string $creditmemoIncrementId
     * @param string $email
     * @param string|null $incrementId
     * @return boolean
     * @throws \Exception
     */
    public function sendEmail($creditmemoIncrementId, $email, $incrementId = '');

    /**
     * @param \Magestore\Webpos\Api\Data\Customer\CustomerInterface $customer
     * @param string $incrementId
     * @return \Magestore\Webpos\Api\Data\Customer\CustomerInterface
     * @throws \Exception
     */
    public function createCustomer($customer, $incrementId);
}
