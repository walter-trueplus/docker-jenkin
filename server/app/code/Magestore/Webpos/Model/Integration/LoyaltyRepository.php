<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\Integration;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;

/**
 * Customer repository.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoyaltyRepository implements \Magestore\Webpos\Api\Integration\LoyaltyRepositoryInterface
{
    /**
     * @var \Magestore\Webpos\Model\Customer\CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var \Magestore\Webpos\Api\Data\Sales\OrderSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magestore\Webpos\Helper\Data
     */
    protected $helper;

    /**
     * LoyaltyRepository constructor.
     * @param \Magestore\Webpos\Model\Customer\CustomerRepository $customerRepository
     * @param \Magestore\Webpos\Api\Data\Sales\OrderSearchResultInterfaceFactory $searchResultsFactory
     * @param \Magestore\Webpos\Helper\Data $helper
     */
    public function __construct(
        \Magestore\Webpos\Model\Customer\CustomerRepository $customerRepository,
        \Magestore\Webpos\Api\Data\Sales\OrderSearchResultInterfaceFactory $searchResultsFactory,
        \Magestore\Webpos\Helper\Data $helper
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $collection */
        $collection = $this->customerRepository->getCustomerCollection($searchCriteria, true);
        $this->getLoyaltyCollection($collection);
        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $searchResults->setTotalCount($collection->getSize());

        $customers = [];
        /** @var \Magento\Customer\Model\Customer $customerModel */
        foreach ($collection as $customerModel) {
            $customers[] = $customerModel->getDataModel();
        }
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($customers);
        return $searchResults;
    }

    /**
     * @param $collection
     * @return mixed
     */
    public function getLoyaltyCollection($collection) {
        if($this->helper->isRewardPointEnable()) {
            $collection->getSelect()->joinLeft(
                array('rewardpoints_customer' => $collection->getTable('rewardpoints_customer')),
                'e.entity_id = rewardpoints_customer.customer_id',
                array(
                    'rewardpoints_updated_at' => 'rewardpoints_customer.updated_at',
                    'point_balance' => 'rewardpoints_customer.point_balance'
                ));
        }
        if($this->helper->isStoreCreditEnable()) {
            $collection->getSelect()->joinLeft(
                array('customer_credit' => $collection->getTable('customer_credit')),
                'e.entity_id = customer_credit.customer_id',
                array(
                    'customer_credit_updated_at' => 'customer_credit.updated_at',
                    'credit_balance' => 'customer_credit.credit_balance'
                ));
        }
        return $collection;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $collection
    ) {
        $updatedAt = '';
        foreach ($filterGroup->getFilters() as $filter) {
            if($filter->getField() == 'updated_at') {
                $updatedAt = $filter->getValue();
                break;
            }
        }
        if ($updatedAt) {
            if($this->helper->isRewardPointEnable() && $this->helper->isStoreCreditEnable()) {
                $collection->getSelect()->where('
                    rewardpoints_customer.updated_at >= "' . $updatedAt . '" OR
                    customer_credit.updated_at >= "' . $updatedAt . '"
                ');
            } else if($this->helper->isRewardPointEnable()) {
                $collection->getSelect()->where('
                    rewardpoints_customer.updated_at >= "' . $updatedAt . '"
                ');
            } else if($this->helper->isStoreCreditEnable()) {
                $collection->getSelect()->where('
                    customer_credit.updated_at >= "' . $updatedAt . '"
                ');
            }
        }
    }
}
