<?php

namespace Magestore\Webpos\Model\Sales\Order;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;

class ShipmentRepository extends \Magento\Sales\Model\Order\ShipmentRepository implements \Magestore\Webpos\Api\Sales\Order\ShipmentRepositoryInterface {
    /**
    * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
    */
    protected $shipmentLoader;
    /**
    * @var \Magento\Framework\DB\Transaction
    */
    protected $dbTransaction;

    /**
    * @var ShipmentSender
    */
    protected $shipmentSender;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ShipmentFactory
     */
    protected $_shipmentFactory;
    /**
     *
     * @var TrackFactory
     */
    protected $_trackFactory;

    /**
     * @inheritdoc
     */
    public function createShipmentByOrderId($order_id) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->orderRepository = $objectManager->get('Magento\Sales\Api\OrderRepositoryInterface');
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($order_id);
        } catch(\Exception $exception) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Order is not found.'));
        }
        $this->createShipment($order);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param array $items_to_ship
     * @param array $tracks
     * @throws LocalizedException
     */
    protected function createShipment($order, $items_to_ship = [], $tracks = []) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_trackFactory = $objectManager->create('Magento\Sales\Model\Order\Shipment\Track');
        try {
            if(!count($items_to_ship)) {
                $items_to_ship = $this->prepareItemShipment($order);
            }
            $shipment = $this->_prepareShipment($order, $items_to_ship);
            if ($shipment) {
                $shipment->setEmailSent(true);
                $shipment->getOrder()->setCustomerNoteNotify(true);
                if (count($tracks) > 0) {
                    foreach ($tracks as $track){
                        $shipment->addTrack(
                            $this->_trackFactory
                                ->setNumber($track->getNumber())
                                ->setCarrierCode($track->getCarrierCode())
                                ->setTitle($track->getTitle())
                        );
                    }
                }
                $shipment->getOrder()->setIsInProcess(true);
                $transaction = $objectManager->create('Magento\Framework\DB\Transaction');
                $transaction->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();
            } else {

            }
        }catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function prepareItemShipment($order) {
        $data = [];
        foreach ($order->getAllItems() as $item) {
            $data[$item->getId()] = $item->getQtyToShip();
        }
        return $data;
    }

    /**
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $items
     * @param array $tracking
     * @return \Magento\Sales\Api\Data\ShipmentInterface object
     */
    protected function _prepareShipment($order, $items = array(),$tracking = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_shipmentFactory = $objectManager->create('Magento\Sales\Model\Order\ShipmentFactory');
        $shipment = $this->_shipmentFactory->create(
            $order,
            $items,
            $tracking
        );
        if (!$shipment->getTotalQty()) {
            return false;
        }
        return $shipment->register();
    }
}