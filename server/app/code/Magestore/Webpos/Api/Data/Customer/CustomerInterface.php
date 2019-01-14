<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Api\Data\Customer;

/**
 * Customer interface.
 */
interface CustomerInterface extends \Magento\Customer\Api\Data\CustomerInterface
{
    const TELEPHONE = 'telephone';
    const SUBSCRIBER_STATUS = 'subscriber_status';
    const FULL_NAME = 'full_name';
    const SEARCH_STRING = 'search_string';
    const POINT_BALANCE = 'point_balance';
    const CREDIT_BALANCE = 'credit_balance';
    const IS_CREATING = 'is_creating';
    const TMP_CUSTOMER_ID = 'tmp_customer_id';
    /**
     * Get customer telephone
     *
     * @return string|null
     */
    public function getTelephone();

    /**
     * Set customer telephone
     *
     * @param string $telephone
     * @return CustomerInterface
     */
    public function setTelephone($telephone);
    /**
     * Get subscriber
     *
     * @return int|null
     */
    public function getSubscriberStatus();

    /**
     * Set subscriber
     *
     * @api
     * @param int $subscriberStatus
     * @return CustomerInterface
     */
    public function setSubscriberStatus($subscriberStatus);

    /**
     * Get full name
     *
     * @return string|null
     */
    public function getFullName();

    /**
     * Set full name
     *
     * @param string $fullName
     * @return CustomerInterface
     */
    public function setFullName($fullName);
    /**
     * Get search string
     *
     * @return string|null
     */
    public function getSearchString();

    /**
     * Get point balance
     *
     * @return int|null
     */
    public function getPointBalance();

    /**
     * Get credit balance
     *
     * @return float|null
     */
    public function getCreditBalance();

    /**
     * Set customer is creating
     *
     * @param string $isCreating
     * @return CustomerInterface
     */
    public function setIsCreating($isCreating);

    /**
     * Get customer is creating
     *
     * @return boolean
     */
    public function getIsCreating();

}
