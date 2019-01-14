<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\Sales\Order;
/**
 * Class CreditmemoRepository
 * @package Magestore\Webpos\Model\Sales\Order
 */
class CreditmemoRepository implements \Magestore\Webpos\Api\Sales\Order\CreditmemoRepositoryInterface
{
    /**
     * @var \Magento\Sales\Model\Service\CreditmemoService
     */
    protected $creditmemoService;

    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Sales\Api\CreditmemoManagementInterface
     */
    protected $creditmemoManagement;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepositoryInterface;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Sales\Model\Order\RefundAdapterInterface
     */
    private $refundAdapter;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    private $creditmemo;

    /**
     * @var \Magestore\Webpos\Model\Sales\Order\Creditmemo\EmailSender
     */
    private $creditmemoSender;

    /**
     * @var \Magestore\Webpos\Model\Customer\CustomerRepository
     */
    private $customerRepository;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    private $orderRepository;

    /**
     * @var \Magestore\Webpos\Api\Sales\OrderRepositoryInterface
     */
    private $orderRepositoryInterface;

    /**
     * @var \Magestore\Webpos\Api\Data\Checkout\OrderInterfaceFactory
     */
    protected $posOrderInterfaceFactory;

    /**
     * @var \Magestore\Webpos\Api\Staff\SessionRepositoryInterface
     */
    protected $sessionRepository;

    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magestore\Webpos\Api\Data\Checkout\Order\PaymentInterfaceFactory
     */
    protected $paymentInterfaceFactory;

    /**
     * CreditmemoRepository constructor.
     * @param \Magento\Sales\Model\Service\CreditmemoService $creditmemoService
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepositoryInterface
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Model\Order\RefundAdapterInterface $refundAdapter
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @param Creditmemo\EmailSender $creditmemoSender
     * @param \Magestore\Webpos\Model\Customer\CustomerRepository $customerRepository
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Magestore\Webpos\Api\Sales\OrderRepositoryInterface $orderRepositoryInterface
     * @param \Magestore\Webpos\Api\Data\Checkout\OrderInterfaceFactory $posOrderInterfaceFactory
     * @param \Magestore\Webpos\Api\Staff\SessionRepositoryInterface $sessionRepository
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\Webpos\Api\Data\Checkout\Order\PaymentInterfaceFactory $paymentInterfaceFactory
     */
    public function __construct(
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepositoryInterface,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Model\Order\RefundAdapterInterface $refundAdapter,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo,
        \Magestore\Webpos\Model\Sales\Order\Creditmemo\EmailSender $creditmemoSender,
        \Magestore\Webpos\Model\Customer\CustomerRepository $customerRepository,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magestore\Webpos\Api\Sales\OrderRepositoryInterface $orderRepositoryInterface,
        \Magestore\Webpos\Api\Data\Checkout\OrderInterfaceFactory $posOrderInterfaceFactory,
        \Magestore\Webpos\Api\Staff\SessionRepositoryInterface $sessionRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Webpos\Api\Data\Checkout\Order\PaymentInterfaceFactory $paymentInterfaceFactory
    )
    {
        $this->creditmemoService = $creditmemoService;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->creditmemoLoader = $creditmemoLoader;
        $this->request = $request;
        $this->creditmemoManagement = $creditmemoManagement;
        $this->resource = $resource;
        $this->invoiceRepositoryInterface = $invoiceRepositoryInterface;
        $this->priceCurrency = $priceCurrency;
        $this->refundAdapter = $refundAdapter;
        $this->creditmemo = $creditmemo;
        $this->creditmemoSender = $creditmemoSender;
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->posOrderInterfaceFactory = $posOrderInterfaceFactory;
        $this->sessionRepository = $sessionRepository;
        $this->coreRegistry = $coreRegistry;
        $this->objectManager = $objectManager;
        $this->paymentInterfaceFactory = $paymentInterfaceFactory;
    }

    /**
     * Prepare creditmemo to refund and save it.
     *
     * @param \Magestore\Webpos\Api\Data\Sales\Order\CreditmemoInterface $creditmemo
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createCreditmemoByOrderId($creditmemo)
    {
        $sessionId = $this->request->getParam(\Magestore\WebposIntegration\Controller\Rest\RequestProcessor::SESSION_PARAM_KEY);
        $sessionLogin = $this->sessionRepository->getBySessionId($sessionId);
        $this->coreRegistry->register('create_creditmemo_webpos', true);
        $this->coreRegistry->register('current_location_id', $sessionLogin->getLocationId());

        $creditmemoOffline = $creditmemo;
        $orderId = $creditmemo->getOrderId();
        $payments = $creditmemo->getPayments();
        $this->validateForRefund($creditmemoOffline);
        $data = $this->prepareCreditmemo($creditmemoOffline);
        $this->creditmemoLoader->setOrderId($data['order_id']);
        $this->creditmemoLoader->setCreditmemo($data['creditmemo']);
        $this->request->setParams($data);
        $creditmemo = $this->creditmemoLoader->load();
        if ($creditmemo) {
            if (!$creditmemo->isValidGrandTotal()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The credit memo\'s total must be positive.')
                );
            }
            if (!empty($data['creditmemo']['comment_text'])) {
                foreach ($data['creditmemo']['comment_text'] as $commentText) {
                    $creditmemo->addComment(
                        $commentText,
                        isset($data['creditmemo']['comment_customer_notify']),
                        true
                    );
                }
                if (isset($data['creditmemo']['comment_text'][0]))
                    $creditmemo->setCustomerNote($data['creditmemo']['comment_text'][0]);
                if (isset($data['creditmemo']['comment_customer_notify']))
                    $creditmemo->setCustomerNoteNotify(isset($data['comment_customer_notify']));
            }
            if ($creditmemoOffline->getIncrementId()) {
                $creditmemo->setIncrementId($creditmemoOffline->getIncrementId());
            }
            $creditmemo->setPosLocationId($sessionLogin->getLocationId());
            $this->creditmemoManagement->refund($creditmemo, true, !empty($data['creditmemo']['send_email']));

            $order = $creditmemo->getOrder();

            if ($payments && count($payments)) {
                $this->createWebposOrderPayment($order, $payments);
            }

            if (!empty($data['creditmemo']['send_email'])) {
                $this->creditmemoSender->send($creditmemo);
            }
        }

        $order = $this->orderRepositoryInterface->get($orderId);
        return $this->verifyOrderReturn($order);
    }

    /**
     * @param \Magestore\Webpos\Api\Data\Sales\Order\CreditmemoInterface $creditmemo
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateForRefund($creditmemo)
    {
        if ($creditmemo->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We cannot register an existing credit memo.')
            );
        }
    }

    /**
     * @param \Magestore\Webpos\Api\Data\Sales\Order\CreditmemoInterface $creditmemo
     * @return array
     */
    protected function prepareCreditmemo(\Magestore\Webpos\Api\Data\Sales\Order\CreditmemoInterface $creditmemo)
    {
        $data = [];
        $items = $creditmemo->getItems();
        $orderId = $creditmemo->getOrderId();
        if (count($items) > 0 && $orderId) {
            $data['order_id'] = $orderId;
            $creditmemoData = [];
            foreach ($items as $item) {
                $creditmemoData['items'][$item->getOrderItemId()]['qty'] = $item->getQty();
                if ($item->getBackToStock()) {
                    $creditmemoData['items'][$item->getOrderItemId()]['back_to_stock'] = 1;
                }
            }
            $creditmemoData['send_email'] = $creditmemo->getEmailSent();
            $comments = $creditmemo->getComments();
            if (count($comments)) {
                foreach ($comments as $comment) {
                    if (!isset($creditmemoData['comment_text'])) {
                        $creditmemoData['comment_text'] = [$comment->getComment()];
                    } else {
                        $creditmemoData['comment_text'][] = $comment->getComment();
                    }
                }
            }
            if ($creditmemoData['send_email'])
                $creditmemoData['comment_customer_notify'] = 1;
            /*$creditmemoData['shipping_amount'] = $creditmemo->getBaseShippingAmount();*/
            /** @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
            $taxConfig = $this->objectManager->create('Magento\Tax\Model\Config');
            if ($taxConfig->displaySalesShippingInclTax($creditmemo->getOrder()->getStoreId())) {
                $creditmemoData['shipping_amount'] = $creditmemo->getBaseShippingInclTax();
            } else {
                $creditmemoData['shipping_amount'] = $creditmemo->getBaseShippingAmount();
            }
            $creditmemoData['adjustment_positive'] = $creditmemo->getBaseAdjustmentPositive();
            $creditmemoData['adjustment_negative'] = $creditmemo->getBaseAdjustmentNegative();
            $creditmemoData['refund_earned_points'] = $creditmemo->getRefundEarnedPoints();
            $creditmemoData['refund_points'] = $creditmemo->getRefundPoints();
            $data['creditmemo'] = $creditmemoData;
        }
        return $data;
    }

    /**
     * @param int $creditmemoIncrementId
     * @param string $email
     * @param string|null $incrementId
     * @return bool
     */
    public function sendEmail($creditmemoIncrementId, $email, $incrementId = '')
    {
        $creditmemo = $this->getByIncrementId($creditmemoIncrementId);
        if ($creditmemo) {
            $emailSender = $this->creditmemoSender;
            return $emailSender->sendCreditmemoToAnotherEmail($creditmemo, $email);
        }
        return false;
    }

    /**
     * Loads a specified credit memo.
     *
     * @param int $incrementId The credit memo Increment Id.
     * @return \Magento\Sales\Api\Data\CreditmemoInterface|null Credit memo interface.
     */
    public function getByIncrementId($incrementId)
    {
        $creditmemo = $this->creditmemo->load($incrementId, 'increment_id');
        if ($creditmemo->getId()) {
            return $creditmemo;
        }
        return null;
    }

    /**
     * @param \Magestore\Webpos\Api\Data\Customer\CustomerInterface $customer
     * @param string $incrementId
     * @return \Magestore\Webpos\Api\Data\Customer\CustomerInterface
     * @throws \Exception
     */
    public function createCustomer($customer, $incrementId)
    {
        if (!$customer->getId()) {
            $customer = $this->customerRepository->save($customer);
        }
        if ($customer->getId() && $incrementId) {
            $order = $this->orderRepositoryInterface->getMagentoOrderByIncrementId($incrementId);
            $order->setCustomerId($customer->getId());
            $order->setCustomerEmail($customer->getEmail());
            $order->setCustomerGroupId($customer->getGroupId());
            $order->setCustomerFirstname($customer->getFirstname());
            $order->setCustomerLastname($customer->getLastname());
            $order->setCustomerIsGuest(0);
            try {
                $this->orderRepository->save($order);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
            return $customer;
        } else {
            throw new \Exception('Can not create customer');
        }
    }

    /**
     * get correct status from order
     * @param \Magestore\Webpos\Api\Data\Checkout\OrderInterface $order
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     */
    protected function verifyOrderReturn($order)
    {
        $posOrder = $this->posOrderInterfaceFactory->create();
        $posOrder->setData($order->getData());
        $posOrder->setAddresses($order->getAddresses());
        $posOrder->setPayments($order->getPayments());
        $posOrder->setItems($order->getItems());
        $posOrder->setStatusHistories($order->getStatusHistories());
        return $posOrder;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Magestore\Webpos\Api\Data\Checkout\Order\PaymentInterface[] $payments
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function createWebposOrderPayment(&$order, $payments)
    {
        if (count($payments)) {
            /** @var \Magestore\Webpos\Api\Data\Checkout\Order\PaymentInterface $payment */
            foreach ($payments as $payment) {
                $data = $payment->getData();
                $data['title'] = $payment->getTitle();
                $data['base_amount_paid'] = $payment->getBaseAmountPaid();
                $data['amount_paid'] = $payment->getAmountPaid();
                /** @var \Magestore\Webpos\Model\Checkout\Order\Payment $paymentModel */
                $paymentModel = $this->paymentInterfaceFactory->create();
                $paymentModel->setData($data);
                $paymentModel->setOrderId($order->getId());
                try {
                    $paymentModel->getResource()->save($paymentModel);
                } catch (\Exception $exception) {
                    throw new \Magento\Framework\Exception\CouldNotSaveException(__($exception->getMessage()));
                }

                if ($data['method'] == 'store_credit' && $order->getCustomerId()) {
                    $this->changeCustomerCredit($data['base_amount_paid'], $order);
                }
            }
        }
    }

    /**
     * @param $creditAmount
     * @param $customerId
     */
    public function changeCustomerCredit($creditAmount, $order)
    {
        $transaction = $this->objectManager->get('\Magestore\Customercredit\Model\TransactionFactory')
            ->create();
        $customerCredit = $this->objectManager->get('Magestore\Customercredit\Model\CustomercreditFactory')
            ->create();
        $customerId = $order->getCustomerId();
        $orderId = $order->getId();
        $transactionDetail = $transaction_detail = __("Refund order #%1", $order->getIncrementId());
        $transaction->addTransactionHistory($customerId, 5, $transactionDetail, $orderId, $creditAmount);
        $customerCredit->changeCustomerCredit($creditAmount, $customerId);
    }
}