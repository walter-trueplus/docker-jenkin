<?php

namespace Magestore\Webpos\Model\Checkout;

use Magento\Framework\Exception\CouldNotSaveException;
use Magestore\Webpos\Api\Data\Checkout\OrderInterface;

class CheckoutRepository implements \Magestore\Webpos\Api\Checkout\CheckoutRepositoryInterface
{
    const STATE_PENDING = 'pending';

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Model\Order\AddressFactory
     */
    protected $addressFactory;
    /**
     * @var \Magento\Customer\Model\Address
     */
    protected $customerAddress;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;
    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentInterface
     */
    protected $orderPaymentInterface;
    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeFactory;
    /**
     * @var \Magestore\Webpos\Model\Sales\OrderRepository
     */
    protected $orderRepository;
    /**
     * @var \Magestore\Webpos\Api\Sales\Order\InvoiceRepositoryInterface
     */
    protected $invoiceRepositoryInterface;
    /**
     * @var \Magestore\Webpos\Api\Sales\Order\ShipmentRepositoryInterface
     */
    protected $shipmentRepositoryInterface;
    /**
     * @var \Magento\Sales\Model\AdminOrder\EmailSender
     */
    protected $emailSender;
    /**
     * @var \Magestore\Webpos\Api\Data\Checkout\Order\PaymentInterfaceFactory
     */
    protected $paymentOrderInterfaceFactory;

    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Magestore\Webpos\Api\Staff\SessionRepositoryInterface
     */
    protected $sessionRepository;
    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Application Event Dispatcher
     *
     * @var \Magestore\Webpos\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\CatalogInventory\Api\StockManagementInterface
     */
    protected $stockManagement;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $stockIndexerProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $priceIndexer;

    /**
     *
     * @var \Magestore\Webpos\Helper\Order
     */
    protected $orderHelper;
    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock
     */
    protected $resourceStock;

    protected $parentItemId = [];
    protected $itemProductId = [];

    /**
     * CheckoutRepository constructor.
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Order\AddressFactory $addressFactory
     * @param \Magento\Customer\Model\Address $customerAddress
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $orderPaymentInterface
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magestore\Webpos\Model\Sales\OrderRepository $orderRepository
     * @param \Magestore\Webpos\Api\Sales\Order\InvoiceRepositoryInterface $invoiceRepositoryInterface
     * @param \Magestore\Webpos\Api\Sales\Order\ShipmentRepositoryInterface $shipmentRepositoryInterface
     * @param \Magento\Sales\Model\AdminOrder\EmailSender $emailSender
     * @param \Magestore\Webpos\Api\Data\Checkout\Order\PaymentInterfaceFactory $paymentOrderInterfaceFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magestore\Webpos\Api\Staff\SessionRepositoryInterface $sessionRepository
     * @param \Magento\Framework\Event\ManagerInterface $_eventManager
     * @param \Magestore\Webpos\Helper\Data $helper
     * @param \Magestore\Webpos\Helper\Order $orderHelper
     */
    public function __construct(
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\AddressFactory $addressFactory,
        \Magento\Customer\Model\Address $customerAddress,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Sales\Api\Data\OrderPaymentInterface $orderPaymentInterface,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magestore\Webpos\Model\Sales\OrderRepository $orderRepository,
        \Magestore\Webpos\Api\Sales\Order\InvoiceRepositoryInterface $invoiceRepositoryInterface,
        \Magestore\Webpos\Api\Sales\Order\ShipmentRepositoryInterface $shipmentRepositoryInterface,
        \Magento\Sales\Model\AdminOrder\EmailSender $emailSender,
        \Magestore\Webpos\Api\Data\Checkout\Order\PaymentInterfaceFactory $paymentOrderInterfaceFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\Webpos\Api\Staff\SessionRepositoryInterface $sessionRepository,
        \Magento\Framework\Event\ManagerInterface $_eventManager,
        \Magestore\Webpos\Helper\Data $helper,
        \Magento\CatalogInventory\Api\StockManagementInterface $stockManagement,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer,
        \Magento\CatalogInventory\Model\ResourceModel\Stock $resourceStock,
        \Magestore\Webpos\Helper\Order $orderHelper
    )
    {
        $this->quoteFactory = $quoteFactory;
        $this->orderFactory = $orderFactory;
        $this->addressFactory = $addressFactory;
        $this->customerAddress = $customerAddress;
        $this->customer = $customer;
        $this->orderPaymentInterface = $orderPaymentInterface;
        $this->orderItemFactory = $orderItemFactory;
        $this->storeManager = $storeManager;
        $this->storeFactory = $storeFactory;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepositoryInterface = $invoiceRepositoryInterface;
        $this->shipmentRepositoryInterface = $shipmentRepositoryInterface;
        $this->emailSender = $emailSender;
        $this->paymentOrderInterfaceFactory = $paymentOrderInterfaceFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->request = $request;
        $this->sessionRepository = $sessionRepository;
        $this->_eventManager = $_eventManager;
        $this->helper = $helper;
        $this->stockManagement = $stockManagement;
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->priceIndexer = $priceIndexer;
        $this->resourceStock = $resourceStock;
        $this->orderHelper = $orderHelper;
    }

    /**
     * @inheritdoc
     */
    public function sendEmailOrder(\Magento\Sales\Model\Order $order)
    {
        try {
            $this->emailSender->send($order);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function placeOrder(\Magestore\Webpos\Api\Data\Checkout\OrderInterface $order, $create_shipment, $create_invoice)
    {
        /** prevent duplicate order */
        try {
            /** @var \Magestore\Webpos\Api\Data\Checkout\OrderInterface $existedOrder */
            $existedOrder = $this->orderRepository->getWebposOrderByIncrementId($order->getIncrementId());
        } catch (\Exception $e) {
            $existedOrder = false;
        }

        if ($existedOrder) {
            return $this->verifyOrderReturn($existedOrder);
        }

        $newOrder = $this->createOrder($order, true);
        $this->_eventManager->dispatch('sales_order_place_after', ['order' => $newOrder]);
        $this->subtractOrderInventory($newOrder);
        if ($create_invoice) {
            $this->invoiceRepositoryInterface->createInvoiceByOrderId($newOrder->getId());
        }
        if ($create_shipment) {
            $this->shipmentRepositoryInterface->createShipmentByOrderId($newOrder->getId());
        }
        if ($this->helper->getStoreConfig('webpos/checkout/automatically_send_mail')) {
            $this->sendEmailOrder($newOrder);
        }
        // create webpos order payment
        if ($order->getPayments()) {
            $this->createWebposOrderPayment($newOrder, $order->getPayments());
        }

        if($order->getOsPosCustomDiscountReason()){
            $history = $newOrder->addStatusHistoryComment($order->getOsPosCustomDiscountReason());
            $history->save();
        }

        $newOrder = $this->orderRepository->get($newOrder->getId());
        return $this->verifyOrderReturn($newOrder);
    }

    /**
     * @param $order
     * @param bool $addPayment
     * @param bool $status
     * @return \Magento\Sales\Model\Order
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createOrder($order, $addPayment = false, $status = false)
    {
        $this->_coreRegistry->register('create_order_webpos', true);
        $sessionId = $this->request->getParam(\Magestore\WebposIntegration\Controller\Rest\RequestProcessor::SESSION_PARAM_KEY);
        $sessionLogin = $this->sessionRepository->getBySessionId($sessionId);
        $this->_coreRegistry->register('current_location_id', $sessionLogin->getLocationId());
        $this->_coreRegistry->register('pos_fulfill_online', $order->getPosFulfillOnline());
        $payments = $order->getPayments();
        $addresses = $order->getAddresses();
        $items = $order->getItems();

        $order->setEntityId('');
        $order->setQuoteAddressId('');
        $order->setBillingAddressId('');
        $order->setShippingAddressId('');
        if ($status == \Magento\Sales\Model\Order::STATE_HOLDED) {
            $befofreHoldStatus = 'pending';
            $order->setHoldBeforeState(\Magento\Sales\Model\Order::STATE_NEW);
            $order->setHoldBeforeStatus($befofreHoldStatus);
        }
        /**
         * check quote
         */
        $this->checkQuote($order);

        /** @var \Magento\Sales\Model\Order $newOrder */
        $newOrder = $this->orderFactory->create();
        try {
            $data = $order->getData();
            unset($data['items']);
            unset($data['payment']);
            unset($data['addresses']);

            $newOrder->setData($data);

            // add address to order
            $this->addAddressToOrder($newOrder, $addresses);
            if ($addPayment) {
                // add payment to order
                $this->addPaymentToOrder($newOrder, $payments);
            }
            // add item to order
            $this->addItemToOrder($newOrder, $items);

            $storeId = $this->checkStoreId($newOrder->getStoreId());
            $newOrder->setStoreId($storeId);

            $newOrder->save();
            $this->afterCreateNewOrder($newOrder, $order, $items);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $newOrder;
    }

    /**
     * Remove quote id data if quote is not exist
     *
     * @param \Magestore\Webpos\Api\Data\Checkout\OrderInterface $order
     */
    protected function checkQuote(\Magestore\Webpos\Api\Data\Checkout\OrderInterface &$order)
    {
        $quoteId = $order->getQuoteId();
        $quote = $this->quoteFactory->create()->load($quoteId);
        if (!$quote->getId()) {
            $order->setQuoteId('');
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Magestore\Webpos\Api\Data\Checkout\Order\AddressInterface[] $addresses
     */
    protected function addAddressToOrder(&$order, $addresses)
    {
        foreach ($addresses as $address) {
            /** @var \Magento\Sales\Model\Order\Address $add */
            $add = $this->addressFactory->create();
            $data = $address->getData();

            unset($data['entity_id']);
            unset($data['parent_id']);
            unset($data['quote_address_id']);
            $customerAddress = $this->customerAddress->load($data['customer_address_id']);
            if (!$customerAddress->getId()) {
                unset($data['customer_address_id']);
            }
            $customer = $this->customer->load($data['customer_id']);
            if (!$customer->getId()) {
                unset($data['customer_id']);
            }

            if ($address->getAddressType() == \Magento\Sales\Model\Order\Address::TYPE_BILLING) {
                $add->setData($data);
                $order->setBillingAddress($add);
            } else {
                $add->setData($data);
                $add->setAddressType(\Magento\Sales\Model\Order\Address::TYPE_SHIPPING);
                $order->setShippingAddress($add);
            }
        }
    }

    /**
     * create new order
     *
     * @param \Magento\Sales\Model\Order $order
     * @param \Magestore\Webpos\Api\Data\Checkout\Order\PaymentInterface[] $payments
     * @param boolean $reCalculateTotal
     */
    protected function addPaymentToOrder(&$order, $payments, $reCalculateTotal = false)
    {
        if ($order->getId()) {
            $oldOrder = $this->orderFactory->create()->load($order->getId());
        }
        /** @var \Magento\Sales\Api\Data\OrderPaymentInterface $pm */
        $pm = $this->orderPaymentInterface;
        $pm->setMethod('multipaymentforpos');
        $pm->setAmountOrdered($order->getGrandTotal());
        $pm->setBaseAmountOrdered($order->getBaseGrandTotal());
        $pm->setAmountPaid(0);
        $pm->setBaseAmountPaid(0);
        $pm->setBaseShippingAmount(0);
        $pm->setShippingAmount(0);
        $totalPaid = 0;
        $baseTotalPaid = 0;
        $takeBaseAmount = 0;
        $takeAmount = 0;
        if (count($payments)) {
            foreach ($payments as $payment) {
                if ($payment->getIsPayLater())
                    continue;
                $totalPaid += (float)$payment->getAmountPaid();
                $baseTotalPaid += (float)$payment->getBaseAmountPaid();
                if (!$payment->getIsPaid()) {
                    $takeAmount += (float)$payment->getAmountPaid();
                    $takeBaseAmount += (float)$payment->getBaseAmountPaid();
                }
                $pm->setAmountPaid(round((float)$payment->getAmountPaid() + (float)$pm->getAmountPaid(), 4));
                $pm->setBaseAmountPaid(round((float)$payment->getBaseAmountPaid() + (float)$pm->getBaseAmountPaid(), 4));
            }
            if ($reCalculateTotal) {
                if ($order->getPosBasePreTotalPaid() > 0 && $order->getPosPreTotalPaid() > 0) {
                    if ($oldOrder) {
                        $totalPaid = $oldOrder->getTotalPaid() + $takeAmount;
                        $baseTotalPaid = $oldOrder->getBaseTotalPaid() + $takeBaseAmount;
                    } else {
                        $totalPaid = $order->getTotalPaid() + $takeAmount;
                        $baseTotalPaid = $order->getBaseTotalPaid() + $takeBaseAmount;
                    }
                }
                $order->setTotalPaid(min($totalPaid, $order->getGrandTotal()));
                $order->setBaseTotalPaid(min($baseTotalPaid, $order->getBaseGrandTotal()));
                $totalDue = max($order->getGrandTotal() - $totalPaid, 0);
                $baseTotalDue = max($order->getBaseGrandTotal() - $baseTotalPaid, 0);
                $posChange = max($totalPaid - $order->getGrandTotal(), 0);
                $basePosChange = max($totalPaid - $order->getBaseGrandTotal(), 0);
                $order->setTotalDue($totalDue);
                $order->setBaseTotalDue($baseTotalDue);
                $order->setPosChange($posChange);
                $order->setBasePosChange($basePosChange);
            }
        }
        $order->setPayment($pm);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Magestore\Webpos\Api\Data\Checkout\Order\ItemInterface[] $items
     */
    protected function addItemToOrder(&$order, $items)
    {
        $dataItems = [];
        foreach ($items as $item) {
            $id = $item->getItemId();
            $item->setItemId('');
            $item->setOrderId('');
            $item->setQuoteItemId('');

            // check store id
            $storeId = $this->checkStoreId($item->getStoreId());
            $item->setStoreId($storeId);

            $dataItems[$id] = $item->getData();
            $dataItems[$id]['tmp_item_id'] = $id;
        }
        $tmpData = $dataItems;
        $removedData = [];
        foreach ($tmpData as $key => $datum) {
            if ($datum['parent_item_id'] && in_array($datum['parent_item_id'], array_keys($tmpData))) {
                if (!in_array($datum['parent_item_id'], $this->parentItemId)) {
                    $dataItems[$key]['parent_item'] = $dataItems[$datum['parent_item_id']];
                    $removedData[] = $datum['parent_item_id'];
                }
                // check for bundle product
                $this->parentItemId[$datum['tmp_item_id']] = $datum['parent_item_id'];
            }
        }
        // remove data parent item
        foreach ($removedData as $remove) {
            unset($dataItems[$remove]);
        }

        foreach ($dataItems as $key => $dataItem) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            $orderItem = $this->orderItemFactory->create();

            // check store id
            $storeId = $this->checkStoreId($dataItem['store_id']);
            $dataItem['store_id'] = $storeId;

            // check parent item
            /** @var \Magento\Sales\Model\Order\Item $parentItem */
            $parentItem = $this->orderItemFactory->create();
            if (isset($dataItem['parent_item'])) {
                $parentItemData = $dataItem['parent_item'];
                unset($dataItem['parent_item']);

                // check store id
                $storeId = $this->checkStoreId($parentItemData['store_id']);
                $parentItemData['store_id'] = $storeId;

                $parentItem->setData($parentItemData);
            }

            unset($dataItem['parent_item_id']);

            $orderItem->setData($dataItem);

            if ($parentItem->getData()) {
                $order->addItem($parentItem);
                $orderItem->setParentItem($parentItem);
            }

            $order->addItem($orderItem);
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Magestore\Webpos\Api\Data\Checkout\OrderInterface $params
     * @throws CouldNotSaveException
     */
    protected function afterCreateNewOrder($order, $params)
    {
        if ($order->getShippingAddress()) {
            $order->setData('shipping_address_id', $order->getShippingAddress()->getEntityId());
        }
        if ($order->getBillingAddress()) {
            $order->setData('billing_address_id', $order->getBillingAddress()->getEntityId());
        }
        try {
            $order->save();
            $this->checkOrderItems($order->getAllItems());
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
    }

    /**
     * Subtract order items qtys from stock items related with order items products.
     *
     * @param \Magento\Sales\Model\Order $order
     */
    protected function subtractOrderInventory($order)
    {
        $items = $this->getProductQty($order->getAllItems());
        $itemsForReindex = $this->stockManagement->registerProductsSale(
            $items,
            $order->getStore()->getWebsiteId()
        );
        $productIds = [];
        foreach ($itemsForReindex as $item) {
            $item->setData('stock_status_changed_auto', 1);
            $item->save();
            $productIds[] = $item->getProductId();
        };
        if (!empty($productIds)) {
            $this->stockIndexerProcessor->reindexList($productIds);
            $this->priceIndexer->reindexList($productIds);
        }

        // update stock status
        $this->resourceStock->updateSetOutOfStock($order->getStore()->getWebsiteId());
        $this->resourceStock->updateSetInStock($order->getStore()->getWebsiteId());
        $this->resourceStock->updateLowStockDate($order->getStore()->getWebsiteId());
    }

    /**
     * Prepare array with information about used product qty and product stock item
     *
     * @param $relatedItems
     * @return array
     */
    public function getProductQty($relatedItems)
    {
        $items = [];
        foreach ($relatedItems as $item) {
            $productId = $item->getProductId();
            if (!$productId) {
                continue;
            }
            if (isset($items[$productId])) {
                $items[$productId] += $item->getQtyOrdered();
            } else {
                $items[$productId] = $item->getQtyOrdered();
            }
        }
        return $items;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item[] $items
     * @throws \Exception
     */
    protected function checkOrderItems($items)
    {
        foreach ($items as $item) {
            if ($item->getParentItemId() == null && isset($this->parentItemId[$item->getData('tmp_item_id')])) {
                $item->setParentItemId($this->getItemIdByTmpItemId($this->parentItemId[$item->getData('tmp_item_id')]))->save();
            }
        }
    }

    protected function getItemIdByTmpItemId($tmpItemId)
    {
        if (!isset($this->itemProductId[$tmpItemId])) {
            /** @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection $collection */
            $collection = $this->orderItemFactory->create()->getCollection();
            $collection->addFieldToFilter('tmp_item_id', $tmpItemId);
            $collection->addOrder('item_id', 'DESC');

            $this->itemProductId[$tmpItemId] = $collection->getFirstItem()->getId();
        }
        return $this->itemProductId[$tmpItemId];
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Magestore\Webpos\Api\Data\Checkout\Order\PaymentInterface[] $payments
     * @throws CouldNotSaveException
     */
    protected function createWebposOrderPayment(&$order, $payments)
    {
        $orderPayments = [];
        if (count($payments)) {
            foreach ($payments as $payment) {
                if (!$payment->getIsPaid()) {
                    $data = $payment->getData();
                    /** @var \Magestore\Webpos\Model\Checkout\Order\Payment $paymentModel */
                    $paymentModel = $this->paymentOrderInterfaceFactory->create();
                    $paymentModel->setData($data);
                    $paymentModel->setOrderId($order->getId());
                    try {
                        $paymentModel->getResource()->save($paymentModel);
                    } catch (\Exception $exception) {
                        throw new CouldNotSaveException(__($exception->getMessage()));
                    }

                    $orderPayments[] = $paymentModel;
                    if ($data['method'] == 'store_credit' && $order->getCustomerId()) {
                        $this->orderRepository->changeCustomerCredit(-$data['base_amount_paid'], $order);
                    }
                }
            }
        }
        if (count($orderPayments)) {
            $order->setPayments($orderPayments);
        }
    }

    /**
     * @param int $storeId
     * @return int
     */
    protected function checkStoreId($storeId)
    {
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = $this->storeManager;
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeFactory->create();
        if (!$store->load($storeId)->getId()) {
            return $storeManager->getStore()->getId();
        } else {
            return $storeId;
        }
    }

    /**
     * get correct status from order
     * @param \Magento\Sales\Model\Order $order
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     */
    protected function verifyOrderReturn($order)
    {
        return $this->orderHelper->verifyOrderReturn($order);
    }

    /**
     * @inheritdoc
     */
    public function takePayment(
        $payments,
        $incrementId,
        $createInvoice
    )
    {
        $newOrder = $this->orderFactory->create()->load($incrementId, 'increment_id');
        if ($newOrder->getId()) {
            try {
                $this->addPaymentToOrder($newOrder, $payments, true);
                $newOrder->save();
            } catch (\Exception $exception) {
                throw new CouldNotSaveException(__($exception->getMessage()));
            }

            if ($createInvoice) {
                $this->invoiceRepositoryInterface->createInvoiceByOrderId($newOrder->getId());
            }

            $this->sendEmailOrder($newOrder);

            // create webpos order payment
            if ($payments) {
                $this->createWebposOrderPayment($newOrder, $payments, true);
            }
            $newOrder = $this->orderRepository->get($newOrder->getId());
            $this->_eventManager->dispatch('sales_order_place_after', ['order' => $newOrder]);
            return $this->verifyOrderReturn($newOrder);
        } else {
            throw new CouldNotSaveException(__('Order doesn\'t exist'));
        }
    }

    /**
     * @param \Magestore\Webpos\Api\Data\Checkout\OrderInterface $order
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function holdOrder(\Magestore\Webpos\Api\Data\Checkout\OrderInterface $order)
    {
        $newOrder = $this->createOrder($order, true, \Magento\Sales\Model\Order::STATE_HOLDED);
        $this->_eventManager->dispatch('sales_order_place_after', ['order' => $newOrder]);
        $this->subtractOrderInventory($newOrder);
        $holdedOrder = $this->orderRepository->get($newOrder->getId());
        return $holdedOrder;
    }
}