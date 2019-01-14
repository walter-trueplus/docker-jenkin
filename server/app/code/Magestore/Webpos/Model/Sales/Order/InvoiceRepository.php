<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\Sales\Order;
/**
 * Class InvoiceRepository
 * @package Magestore\Webpos\Model\Sales\Order
 */
class InvoiceRepository implements \Magestore\Webpos\Api\Sales\Order\InvoiceRepositoryInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $orderConverter;

    protected $invoiceManagement;

    /**
     * InvoiceRepository constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Convert\Order $orderConverter,
        \Magestore\Webpos\Api\Sales\Order\InvoiceManagementInterface $invoiceManagement
    ){
        $this->orderRepository = $orderRepository;
        $this->transaction = $transaction;
        $this->registry = $registry;
        $this->orderConverter = $orderConverter;
        $this->invoiceManagement = $invoiceManagement;
    }

    /**
     * @param int $orderId
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Exception
     */
    public function createInvoiceByOrderId($orderId) {
        $order = $this->orderRepository->get($orderId);
        $orderInvoice = $this->createInvoiceByOrder($order);
        return $orderInvoice;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Exception
     */
    public function createInvoiceByOrder($order) {
        $totalPaid = $order->getTotalPaid();
        $baseTotalPaid = $order->getBaseTotalPaid();
        try {
            $invoiceItems = $this->getItemQtys($order);
            if ($order->canInvoice()) {
                $invoice = $this->invoiceManagement->prepareInvoice($order, $invoiceItems);
                if ($invoice) {
                    if ($invoice->getTotalQty()) {
                        $this->registry->register('current_invoice', $invoice);
                        $invoice->register();
                        $invoice->getOrder()->setCustomerNoteNotify(true);
                        $invoice->getOrder()->setIsInProcess(true);
                        $invoice->getOrder()->setTotalPaid($totalPaid);
                        $invoice->getOrder()->setBaseTotalPaid($baseTotalPaid);
                        $transactionSave = $this->transaction
                            ->addObject(
                                $invoice
                            )->addObject(
                                $invoice->getOrder()
                            );
                        $transactionSave->save();
                    }
                }else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('The invoice is not exist.'));
                }
            }
        }catch (\Magento\Framework\Exception\LocalizedException $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save the invoice.'));
        }
        return $order;
    }

    /**
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array
     */
    protected function getItemQtys($order) {
        $savedQtys = array();
        $orderItems = $order->getAllItems();
        foreach ($orderItems as $item) {
            if($item->canInvoice()) {
                $savedQtys[$item->getId()] = $item->getQtyToInvoice();
            }
        }
        return $savedQtys;
    }

}