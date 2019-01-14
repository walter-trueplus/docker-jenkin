<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Api\Sales;

/**
 * Interface OrderRepositoryInterface
 * @package Magestore\Webpos\Api\Sales
 */
interface OrderRepositoryInterface
{
    /**
     * Loads a specified order.
     *
     * @param int $id The order ID.
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface WebposOrder interface.
     */
    public function get($id);

    /**
     * Lists orders that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria The search criteria.
     * @return \Magestore\Webpos\Api\Data\Sales\OrderSearchResultInterface Order search result interface.
     */
    public function sync(\Magento\Framework\Api\SearchCriteria $searchCriteria);

    /**
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \Magestore\Webpos\Api\Data\Log\DataLogStringResultsInterface
     */
    public function outOfPermission(\Magento\Framework\Api\SearchCriteria $searchCriteria);

    /**
     * @param string $incrementId
     * @param string $email
     * @return boolean
     * @throws \Exception
     */
    public function sendEmail($incrementId, $email);

    /**
     * @param string $incrementId
     * @param \Magestore\Webpos\Api\Data\Checkout\Order\CommentInterface $comment
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     */
    public function commentOrder($incrementId, $comment);

    /**
     * @param string $incrementId
     * @param \Magestore\Webpos\Api\Data\Checkout\Order\CommentInterface|null $comment
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     * @throws \Exception
     */
    public function cancelOrder($incrementId, $comment);

    /**
     * @param string $incrementId
     * @param \Magestore\Webpos\Api\Data\Checkout\Order\CommentInterface|null $comment
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     * @throws \Exception
     */
    public function unholdOrder($incrementId, $comment);

    /**
     * Delete order
     *
     * @param string $incrementId
     * @return boolean
     * @throws \Exception
     */
    public function deleteOrder($incrementId);

    /**
     * @param string $incrementId
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     * @throws \Exception
     */
    public function getByIncrementId($incrementId);

    /**
     * @param string $incrementId
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     * @throws \Exception
     */
    public function getWebposOrderByIncrementId($incrementId);

    /**
     * @param string $incrementId
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Exception
     */
    public function getMagentoOrderByIncrementId($incrementId);
}
