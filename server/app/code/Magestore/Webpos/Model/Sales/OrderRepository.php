<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\Sales;

use Magento\Sales\Api\Data\OrderInterface;
use Magestore\Webpos\Model\Source\Adminhtml\Since;


/**
 * Class OrderRepository
 * @package Magestore\Webpos\Model\Sales
 */
class OrderRepository implements \Magestore\Webpos\Api\Sales\OrderRepositoryInterface
{
    /**
     * @var \Magestore\Webpos\Api\Data\Sales\OrderSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var \Magestore\Webpos\Model\Checkout\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magestore\Webpos\Model\ResourceModel\Sales\Order\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magestore\Webpos\Helper\Data
     */
    protected $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magestore\Webpos\Model\Sales\Order\EmailSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magestore\Webpos\Helper\Order
     */
    protected $orderHelper;

    /**
     * @var \Magestore\Webpos\Api\Data\Log\DataLogStringResultsInterface $dataLogResults
     */
    protected $dataLogResults;


    /**
     * OrderRepository constructor.
     * @param \Magestore\Webpos\Api\Data\Sales\OrderSearchResultInterfaceFactory $searchResultFactory
     * @param \Magestore\Webpos\Model\Checkout\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\OrderFactory $_orderFactory
     * @param \Magestore\Webpos\Model\ResourceModel\Sales\Order\CollectionFactory $collectionFactory
     * @param \Magestore\Webpos\Helper\Data $helper
     * @param Order\EmailSender $orderSender
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\Webpos\Helper\Order $orderHelper
     * @param \Magestore\Webpos\Api\Data\Log\DataLogStringResultsInterface $dataLogResults
     */
    public function __construct(
        \Magestore\Webpos\Api\Data\Sales\OrderSearchResultInterfaceFactory $searchResultFactory,
        \Magestore\Webpos\Model\Checkout\OrderFactory $orderFactory,
        \Magento\Sales\Model\OrderFactory $_orderFactory,
        \Magestore\Webpos\Model\ResourceModel\Sales\Order\CollectionFactory $collectionFactory,
        \Magestore\Webpos\Helper\Data $helper,
        \Magestore\Webpos\Model\Sales\Order\EmailSender $orderSender,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Webpos\Helper\Order $orderHelper,
        \Magestore\Webpos\Model\Log\DataLogResults $dataLogResults
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->orderFactory = $orderFactory;
        $this->_orderFactory = $_orderFactory;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->orderSender = $orderSender;
        $this->orderManagement = $orderManagement;
        $this->_orderRepository = $orderRepository;
        $this->_objectManager = $objectManager;
        $this->orderHelper = $orderHelper;
        $this->dataLogResults = $dataLogResults;
    }

    /**
     * @param int $id
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     * @throws \Exception
     */
    public function get($id)
    {
        if (!$id) {
            throw new \Exception(__('Id required'));
        }
        /** @var OrderInterface $entity */
        $order = $this->orderFactory->create()->load($id);
        if (!$order->getEntityId()) {
            throw new \Exception(__('Requested entity doesn\'t exist'));
        }
        return $order;
    }

    /**
     * Find entities by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \Magestore\Webpos\Api\Data\Sales\OrderSearchResultInterface Order search result interface.
     * @throws \Exception
     */
    public function sync(\Magento\Framework\Api\SearchCriteria $searchCriteria) {
        $collection = $this->getOrderCollection($searchCriteria);
        $searchResult = $this->searchResultFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \Magestore\Webpos\Api\Data\Log\DataLogStringResultsInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function outOfPermission(\Magento\Framework\Api\SearchCriteria $searchCriteria) {
        // another permission
        $collection = $this->collectionFactory->create();
        /** @var \Magestore\Webpos\Api\Data\Sales\OrderSearchResultInterface $searchResult */
//        $time = time();
        $lastTime = $this->getSearchDays();
        /*$date = strtotime($daySearch, $time);
        $lastTime = date('Y-m-d H:i:s', $date);*/

        $updatedAt = $lastTime;

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() == 'updated_at'){
                    $updatedAt = $filter->getValue();
                }
            }
        }

        /** check permission */
        /**
         * @var \Magestore\Webpos\Model\Config\ConfigRepository $configRepository
         */
        $configRepository = $this->_objectManager->get('\Magestore\Webpos\Model\Config\ConfigRepository');
        $permission = $configRepository->getPermissions();
        $locationId = $this->helper->getCurrentLocationId();
        $this->orderHelper->applyOutOfPermissionForOrderCollect($permission, $collection, $locationId);

        $collection
            ->getSelect()
            ->where('(
                main_table.state != "'.\Magento\Sales\Model\Order::STATE_HOLDED. '" 
                AND main_table.created_at >= "'.$lastTime . '"
                AND main_table.updated_at >= "'.$updatedAt . '"
        )');

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $this->addFilterGroupToCollection($filterGroup, $collection);
        }

        $collection->getSelect()->group('main_table.entity_id');

        $orderIds = array();
        foreach ($collection as $order){
            $orderIds[] = $order->getIncrementId();
        }
        $this->dataLogResults->setIds($orderIds);
        return $this->dataLogResults;
    }

    /**
     * @param $searchCriteria
     * @return mixed
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderCollection($searchCriteria) {
        $collection = $this->collectionFactory->create();
        /** @var \Magestore\Webpos\Api\Data\Sales\OrderSearchResultInterface $searchResult */
//        $time = time();
        $lastTime = $this->getSearchDays();
        /*$date = strtotime($daySearch, $time);
        $lastTime = date('Y-m-d H:i:s', $date);*/

        $holdState = \Magento\Sales\Model\Order::STATE_HOLDED;

        /** check permission */
        /**
         * @var \Magestore\Webpos\Model\Config\ConfigRepository $configRepository
         */
        $configRepository = $this->_objectManager->get('\Magestore\Webpos\Model\Config\ConfigRepository');
        $permission = $configRepository->getPermissions();

        $locationId = $this->helper->getCurrentLocationId();
        $this->orderHelper->applyPermissionForOrderCollect($permission, $collection, $locationId);

        $collection->getSelect()
            ->where('(main_table.state = "'.$holdState . '") OR 
                     (main_table.state != "'.$holdState . '" AND main_table.created_at >= "'.$lastTime . '")');

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $this->addFilterGroupToCollection($filterGroup, $collection);
        }

        if($searchCriteria ->getCurrentPage()) {
            $collection->setCurPage($searchCriteria->getCurrentPage());
        }
        if($searchCriteria ->getPageSize()) {
            $collection->setPageSize($searchCriteria->getPageSize());
        }

        if($searchCriteria->getSortOrders()) {
            foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
                $field = $sortOrder->getField();
                $collection->addOrder(
                    $field,
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        } else {
            $collection->addOrder(
                'created_at',
                'DESC'
            );
        }

        if($this->helper->isRewardPointEnable()) {
            $collection->joinToGetRefundPoint();
        }

        $collection->getSelect()->group('main_table.entity_id');

        return $collection;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $conditions[] = [$condition => $filter->getValue()];
            $fields[] = $filter->getField();
            if ($filter->getConditionType()=='like' && strpos($filter->getValue(),'@')===false){
                $values = explode(' ',$filter->getValue());
                if (count($values>1)){
                    foreach ($values as $value){
                        if (strlen($value) > 2) {
                            $conditions[] = [$condition => $value];
                            $fields[] = $filter->getField();
                        }
                    }
                }
            }
        }
        if ($fields) {
            $searchResult->addFieldToFilter($fields, $conditions);
        }

    }

    /**
     * get period search from config
     *
     * @return string
     */
    public function getSearchDays() {
        $config = $this->helper->getStoreConfig('webpos/offline/order_since');
        $time = time();
        switch ($config) {
            case Since::SINCE_24H:
                $lastTime = $time - 60 * 60 * 24 * 1;
                return date('Y-m-d H:i:s', $lastTime);
            case Since::SINCE_7DAYS:
                $lastTime = $time - 60 * 60 * 24 * 7;
                return date('Y-m-d H:i:s', $lastTime);
            case Since::SINCE_MONTH:
                return date('Y-m-01 00:00:00');
            case Since::SINCE_YTD:
                return date('Y-01-01 00:00:00');
            case Since::SINCE_2YTD:
                $year = date("Y") - 1;
                return date($year . '-01-01 00:00:00');
            default:
                $lastTime = $time - 60 * 60 * 24 * 7;
                return date('Y-m-d H:i:s', $lastTime);
        }
    }

    /**
     * @param string $incrementId
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     * @throws \Exception
     */
    public function getWebposOrderByIncrementId($incrementId)
    {
        if (!$incrementId) {
            throw new \Exception(__('Id required'));
        }
        /** @var \Magestore\Webpos\Api\Data\Checkout\OrderInterface $entity */
        $order = $this->orderFactory->create()->load($incrementId, 'increment_id');
        if (!$order->getEntityId()) {
            throw new \Exception(__('Requested entity doesn\'t exist'));
        }
        return $order;
    }

    /**
     * @param string $incrementId
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     * @throws \Exception
     */
    public function getByIncrementId($incrementId)
    {
        if (!$incrementId) {
            throw new \Exception(__('Id required'));
        }
        /** @var \Magestore\Webpos\Api\Data\Checkout\OrderInterface $order */
        $order = $this->getWebposOrderByIncrementId($incrementId);
        if (!$order->getEntityId()) {
            throw new \Exception(__('Requested entity doesn\'t exist'));
        }
        return $this->orderHelper->verifyOrderReturn($order);
    }

    /**
     * @param string $incrementId
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Exception
     */
    public function getMagentoOrderByIncrementId($incrementId)
    {
        if (!$incrementId) {
            throw new \Exception(__('Id required'));
        }
        /** @var \Magestore\Webpos\Api\Data\Checkout\OrderInterface $order */
        $order = $this->getWebposOrderByIncrementId($incrementId);
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->_orderRepository->get($order->getEntityId());
        if (!$order->getEntityId()) {
            throw new \Exception(__('Requested entity doesn\'t exist'));
        }
        return $order;
    }

    /**
     * @param int $id
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Exception
     */
    public function getById($id)
    {
        if (!$id) {
            throw new \Exception(__('Id required'));
        }
        /** @var OrderInterface $entity */
        $order = $this->_orderFactory->create()->load($id);
        if (!$order->getEntityId()) {
            throw new \Exception(__('Requested entity doesn\'t exist'));
        }
        return $order;
    }

    /**
     * @param string $incrementId
     * @param string $email
     * @return boolean
     * @throws \Exception
     */
    public function sendEmail($incrementId, $email) {
        $order = $this->getMagentoOrderByIncrementId($incrementId);
        $order = $this->getById($order->getEntityId());
        if ($order) {
            $emailSender = $this->orderSender;
            $order->setCustomerEmail($email);
            try {
                $emailSender->send($order);
                return true;
            } catch (\Exception $e) {
                throw new \Exception(__('Can not send email'));
            }
        }
        return true;
    }

    /**
     * @param string $incrementId
     * @param \Magestore\Webpos\Api\Data\Checkout\Order\CommentInterface $comment
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     * @throws \Exception
     */
    public function commentOrder($incrementId, $comment) {
        $order = $this->getMagentoOrderByIncrementId($incrementId);
        if($order) {
            $newOrder = $this->comment($order, $comment);
            return $newOrder;
        } else {
            throw new \Exception(__('Cannot add order comment'));
        }
    }

    /**
     * @param  $order
     * @param  $comment
     * @return $newOrder
     * @throws \Exception
     */
    public function comment($order, $comment) {
        $history = $order->addStatusHistoryComment($comment->getComment(), $order->getStatus());
        $history->setIsVisibleOnFront($comment->getIsVisibleOnFront());
        $history->setCreatedAt($comment->getCreatedAt());
        $history->setIsCustomerNotified(0);
        try {
            $history->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        $newOrder = $this->get($order->getId());
        return $newOrder;
    }

    /**
     * @param string $incrementId
     * @param \Magestore\Webpos\Api\Data\Checkout\Order\CommentInterface $comment
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     * @throws \Exception
     */
    public function cancelOrder($incrementId, $comment) {
       $order = $this->getMagentoOrderByIncrementId($incrementId);
       return $this->cancel($order, $comment);
    }

    /**
     * @param \Magestore\Webpos\Api\Data\Checkout\OrderInterface $order
     * @param \Magestore\Webpos\Api\Data\Checkout\Order\CommentInterface $comment
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     * @throws \Exception
     */
    public function cancel($order, $comment = '') {
        if($order) {
            if ($order->canCancel()) {
                try {
                    $order->cancel();
                    $this->_orderRepository->save($order);
                    if($comment && isset($comment['comment'])) {
                        $this->comment($order, $comment);
                    }

                    $newOrder = $this->get($order->getId());
                    if($this->helper->isStoreCreditEnable() && $newOrder->getCustomerId()) {
                        $this->refundStoreCredit($newOrder);
                    }
                    return $newOrder;
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }
            }
        }
        throw new \Exception(__('Cannot cancel order'));
    }

    /**
     * @param string $incrementId
     * @param \Magestore\Webpos\Api\Data\Checkout\Order\CommentInterface $comment
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     * @throws \Exception
     */
    public function unholdOrder($incrementId, $comment = '') {
        $order = $this->getMagentoOrderByIncrementId($incrementId);
        if($order && $order->getStatus() == \Magento\Sales\Model\Order::STATE_HOLDED) {
            try {
                if ($order->canUnhold()) {
                    $order->unhold();
                    $this->_orderRepository->save($order);
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Can\'t unhold order.'));
                }

                if ($order->canCancel()) {
                    $order->cancel();
                    $this->_orderRepository->save($order);
                }

                if($comment && isset($comment['comment'])) {
                    $this->comment($order, $comment);
                }

                $newOrder = $this->get($order->getId());
                if($this->helper->isStoreCreditEnable() && $newOrder->getCustomerId()) {
                    $this->refundStoreCredit($newOrder);
                }
                return $newOrder;
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
        throw new \Exception(__('Cannot cancel order'));
    }

    /**
     * Delete order
     *
     * @param string $incrementId
     * @return boolean
     * @throws \Exception
     */
    public function deleteOrder($incrementId) {
        $order = $this->getMagentoOrderByIncrementId($incrementId);
        if($order && $order->getStatus() == \Magento\Sales\Model\Order::STATE_HOLDED) {
            $this->_orderRepository->delete($order);
            return true;
        }
        throw new \Exception(__('Cannot delete order'));

    }

    /**
     * @param $order
     * @param string $creditAmount
     */
    public function refundStoreCredit($order, $creditAmount = '') {
        $payments = $order->getPayments();
        foreach ($payments as $payment) {
            if($payment['method'] == 'store_credit') {
                if(!$creditAmount) {
                    $creditAmount = $payment['base_amount_paid'];
                }
                $transactionDetail = __('Cancel order #') . $order->getIncrementId();
                $this->changeCustomerCredit($creditAmount, $order, $transactionDetail);
            }
        }
    }

    /**
     * @param $creditAmount
     * @param $customerId
     */
    public function changeCustomerCredit($creditAmount, $order, $transactionDetail = '') {
        $transaction = $this->_objectManager->get('\Magestore\Customercredit\Model\TransactionFactory')
            ->create();
        $customerId = $order->getCustomerId();
        $incrementId = $order->getIncrementId();
        $orderId = $order->getId();
        if(!$transactionDetail) {
            $transactionDetail = __('Check out by credit for order #') . $incrementId;
        }
        $transaction->addTransactionHistory($customerId, 6, $transactionDetail, $orderId, $creditAmount);
        $customerCredit = $this->_objectManager->get('Magestore\Customercredit\Model\CustomercreditFactory')
            ->create()->load($customerId, 'customer_id');;
        $beginBalance = $customerCredit->getCreditBalance();
        if(!$customerCredit->getCustomerId()){
            $customerCredit->setCustomerId($customerId);
        }
        try {
            $customerCredit->setCreditBalance($beginBalance + $creditAmount);
            $customerCredit->setUpdatedAt(date("Y-m-d H:i:s"));
            $customerCredit->save();
        } catch (\Exception $e) {
        }
    }

}
