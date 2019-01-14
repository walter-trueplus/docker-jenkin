<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\Log;
/**
 * Class DataLogRepository
 * @package Magestore\Webpos\Model\Log
 */
class DataLogRepository implements \Magestore\Webpos\Api\Log\DataLogRepositoryInterface
{
    /**
     * @var \Magestore\Webpos\Model\ResourceModel\Log\ProductDeleted\CollectionFactory
     */
    protected $productDeletedCollection;

    /**
     * @var \Magestore\Webpos\Model\ResourceModel\Log\CustomerDeleted\CollectionFactory
     */
    protected $customerDeletedCollection;

    /**
     * @var \Magestore\Webpos\Model\ResourceModel\Log\OrderDeleted\CollectionFactory
     */
    protected $orderDeletedCollection;

    /**
     * @var \Magestore\Webpos\Api\Data\Log\DataLogResultsInterface
     */
    protected $dataLogResults;
    /**
     * DataLogRepository constructor.
     * @param \Magestore\Webpos\Model\ResourceModel\Log\ProductDeleted\CollectionFactory $productDeletedCollection
     * @param \Magestore\Webpos\Model\ResourceModel\Log\CustomerDeleted\CollectionFactory $customerDeletedCollection
     * @param \Magestore\Webpos\Api\Data\Log\DataLogResultsInterface $dataLogResults
     */
    public function __construct(
        \Magestore\Webpos\Model\ResourceModel\Log\ProductDeleted\CollectionFactory $productDeletedCollection,
        \Magestore\Webpos\Model\ResourceModel\Log\CustomerDeleted\CollectionFactory $customerDeletedCollection,
        \Magestore\Webpos\Model\ResourceModel\Log\OrderDeleted\CollectionFactory $orderDeletedCollection,
        \Magestore\Webpos\Api\Data\Log\DataLogResultsInterface $dataLogResults
    ){
        $this->customerDeletedCollection = $customerDeletedCollection;
        $this->productDeletedCollection = $productDeletedCollection;
        $this->orderDeletedCollection = $orderDeletedCollection;
        $this->dataLogResults = $dataLogResults;
    }

    /**
     * Retrieve data matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magestore\Webpos\Api\Data\Log\DataLogResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListCustomer(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria){
        $customerDeletedCollection = $this->customerDeletedCollection->create();

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                if ($filter->getField() == 'updated_at'){
                    $customerDeletedCollection->addFieldToFilter('deleted_at', [$condition => $filter->getValue()]);
                }
            }
        }

        $customerDeletedIds = array();
        foreach ($customerDeletedCollection as $customer){
            $customerDeletedIds[] = (int)$customer->getCustomerId();
        }
        $this->dataLogResults->setIds($customerDeletedIds);
        return $this->dataLogResults;
    }

    /**
     * Retrieve data matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magestore\Webpos\Api\Data\Log\DataLogResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListProduct(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria){
        $productDeletedCollection = $this->productDeletedCollection->create();

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                if ($filter->getField() == 'updated_at'){
                    $productDeletedCollection->addFieldToFilter('deleted_at', [$condition => $filter->getValue()]);
                }
            }
        }

        $productDeletedIds = array();
        foreach ($productDeletedCollection as $product){
            $productDeletedIds[] = (int)$product->getProductId();
        }
        $this->dataLogResults->setIds($productDeletedIds);
        return $this->dataLogResults;
    }

    /**
     * Retrieve data matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magestore\Webpos\Api\Data\Log\DataLogResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListOrder(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria) {
        $orderDeletedCollection = $this->orderDeletedCollection->create();

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                if ($filter->getField() == 'updated_at'){
                    $orderDeletedCollection->addFieldToFilter('deleted_at', [$condition => $filter->getValue()]);
                }
            }
        }

        $orderDeletedIds = array();
        foreach ($orderDeletedCollection as $order){
            $orderDeletedIds[] = $order->getOrderIncrementId();
        }
        $this->dataLogResults->setIds($orderDeletedIds);
        return $this->dataLogResults;
    }
}
