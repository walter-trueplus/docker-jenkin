<?php

namespace Magestore\Webpos\Observer\Product;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ProductAttributeUpdate implements ObserverInterface
{

    protected $productDeletedInterface;
    protected $productDeletedRepository;
    protected $productDeletedFactory;
    protected $productDeletedResource;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    public function __construct(
        \Magestore\Webpos\Api\Log\ProductDeletedRepositoryInterface $productDeletedRepository,
        \Magestore\Webpos\Api\Data\Log\ProductDeletedInterface $productDeletedInterface,
        \Magestore\Webpos\Model\Log\ProductDeletedFactory $productDeletedFactory,
        \Magestore\Webpos\Model\ResourceModel\Log\ProductDeleted $productDeletedResource,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->productDeletedRepository = $productDeletedRepository;
        $this->productDeletedInterface = $productDeletedInterface;
        $this->productDeletedFactory = $productDeletedFactory;
        $this->productDeletedResource = $productDeletedResource;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(EventObserver $observer)
    {
        $productIds = $observer->getProductIds();
        $attributesData = $observer->getAttributesData();
        if ((isset($attributesData['status']) && $attributesData['status'] == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED)
            || (isset($attributesData['webpos_visible']) && $attributesData['webpos_visible'] != \Magestore\Webpos\Model\ResourceModel\Catalog\Product\Collection::VISIBLE_ON_WEBPOS)
        ) {
            foreach ($productIds as $productId) {
                $productDeleted = $this->productDeletedFactory->create()->load($productId, 'product_id');
                if (!$productDeleted->getId()) {
                    $productDeleted->setProductId($productId);
                    $this->productDeletedRepository->save($productDeleted);
                }
            }
        } else if ((isset($attributesData['status']) && $attributesData['status'] == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            || (isset($attributesData['webpos_visible']) && $attributesData['webpos_visible'] == \Magestore\Webpos\Model\ResourceModel\Catalog\Product\Collection::VISIBLE_ON_WEBPOS)
        ) {
            foreach ($productIds as $productId) {
                $productDeleted = $this->productDeletedFactory->create()->load($productId, 'product_id');
                if ($productDeleted->getId()) {
                    $this->productDeletedResource->delete($productDeleted);
                }
            }
        }
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('catalog_product_entity'); //gives table name with prefix
        try {
            $connection->update(
                $tableName,
                ['updated_at' => date('Y-m-d H:i:s')],
                $connection->quoteInto("entity_id IN (?)", $productIds)
            );
        } catch (\Exception $e) {
            $productIds = $observer->getProductIds();
        }
    }
}