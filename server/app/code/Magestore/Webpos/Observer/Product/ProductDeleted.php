<?php

namespace Magestore\Webpos\Observer\Product;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ProductDeleted implements ObserverInterface {

    protected $productDeletedInterfaceFactory;
    protected $productDeletedRepository;

    public function __construct(
        \Magestore\Webpos\Api\Log\ProductDeletedRepositoryInterface $productDeletedRepository,
        \Magestore\Webpos\Api\Data\Log\ProductDeletedInterfaceFactory $productDeletedInterfaceFactory
    ){
        $this->productDeletedRepository = $productDeletedRepository;
        $this->productDeletedInterfaceFactory = $productDeletedInterfaceFactory;
    }

    public function execute(EventObserver $observer)
    {
        $productId = $observer->getProduct()->getId();
        $productDeleted = $this->productDeletedInterfaceFactory->create()->setProductId($productId);
        $this->productDeletedRepository->save($productDeleted);
    }
}