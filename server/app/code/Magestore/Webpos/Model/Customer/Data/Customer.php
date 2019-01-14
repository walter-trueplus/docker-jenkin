<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\Customer\Data;

/**
 * Class Customer
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Customer extends \Magento\Customer\Model\Data\Customer implements \Magestore\Webpos\Api\Data\Customer\CustomerInterface
{
    /**
     * Get customer billing telephone
     *
     * @return string|null
     */
    public function getTelephone()
    {
        return $this->_get(self::TELEPHONE);
    }

    /**
     * Set customer billing telephone
     *
     * @param string $telephone
     * @return $this
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    /**
     * Get subscriber
     *
     * @return int|null
     */
    public function getSubscriberStatus()
    {
        return $this->_get(self::SUBSCRIBER_STATUS);
    }

    /**
     * Set subscriber
     *
     * @param int $subscriberStatus
     * @return $this
     */
    public function setSubscriberStatus($subscriberStatus)
    {
        return $this->setData(self::SUBSCRIBER_STATUS, $subscriberStatus);
    }

    /**
     * Get full name
     *
     * @return string|null
     */
    public function getFullName()
    {
        return $this->_get(self::FULL_NAME);
    }

    /**
     * Set full name
     *
     * @param string $fullName
     * @return $this
     */
    public function setFullName($fullName)
    {
        return $this->setData(self::FULL_NAME, $fullName);
    }

    /**
     * Get search string
     *
     * @api
     * @return string|null
     */
    public function getSearchString()
    {
        $searchString = $this->_get(self::EMAIL) . ' ' . $this->getFirstname() . ' ' . $this->getLastname() . ' ' . $this->_get(self::TELEPHONE);
        return $searchString;
    }

    /**
     * Get credit balance
     *
     * @return float|null
     */
    public function getCreditBalance()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('\Magestore\Webpos\Helper\Data');
        if ($helper->isStoreCreditEnable()) {
            $customerId = $this->getId();
            $customerCredit = $objectManager->get('\Magestore\Customercredit\Model\CustomercreditFactory')
                ->create();
            $credit = $customerCredit->load($customerId, 'customer_id');
            if ($credit->getId()) {
                $creditBalance = $credit->getCreditBalance();
                return $creditBalance;
            }
        }
        return 0;
    }

    /**
     * Get point balance
     *
     * @return int|null
     */
    public function getPointBalance()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('\Magestore\Webpos\Helper\Data');
        if ($helper->isRewardPointEnable()) {
            $customerId = $this->getId();
            $customerRewardpoint = $objectManager->get('\Magestore\Rewardpoints\Model\CustomerFactory')
                ->create();
            $rewardPoint = $customerRewardpoint->load($customerId, 'customer_id');
            if ($rewardPoint->getId()) {
                $pointBalance = $rewardPoint->getPointBalance();
                return $pointBalance;
            }
        }
        return 0;
    }

    /**
     * Set customer is creating
     *
     * @param string $isCreating
     * @return CustomerInterface
     */
    public function setIsCreating($isCreating)
    {
        return $this;
    }

    /**
     * Get customer is creating
     *
     * @return boolean
     */
    public function getIsCreating()
    {
        return false;
    }
}
