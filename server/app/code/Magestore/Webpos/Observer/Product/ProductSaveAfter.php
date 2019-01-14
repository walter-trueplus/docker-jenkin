<?php

namespace Magestore\Webpos\Observer\Product;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * @var \Magestore\Webpos\Api\Data\Log\ProductDeletedInterface
     */
    protected $productDeletedInterface;
    /**
     * @var \Magestore\Webpos\Api\Log\ProductDeletedRepositoryInterface
     */
    protected $productDeletedRepository;
    /**
     * @var \Magestore\Webpos\Model\Log\ProductDeletedFactory
     */
    protected $productDeletedFactory;
    /**
     * @var \Magestore\Webpos\Model\ResourceModel\Log\ProductDeleted
     */
    protected $productDeletedResource;
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $productType;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Product Type Instances cache
     *
     * @var array
     */
    protected $productTypes = [];

    public function __construct(
        \Magestore\Webpos\Api\Log\ProductDeletedRepositoryInterface $productDeletedRepository,
        \Magestore\Webpos\Api\Data\Log\ProductDeletedInterface $productDeletedInterface,
        \Magestore\Webpos\Model\Log\ProductDeletedFactory $productDeletedFactory,
        \Magestore\Webpos\Model\ResourceModel\Log\ProductDeleted $productDeletedResource,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->productDeletedRepository = $productDeletedRepository;
        $this->productDeletedInterface = $productDeletedInterface;
        $this->productDeletedFactory = $productDeletedFactory;
        $this->productDeletedResource = $productDeletedResource;
        $this->productType = $productType;
        $this->productMetadata = $productMetadata;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(EventObserver $observer)
    {
        $product = $observer->getProduct();
        $productId = $product->getId();

        if(version_compare($this->productMetadata->getVersion(), '2.1.13', '<=')){
            try{
                $connection = $this->resourceConnection->getConnection();
                $tableName = $this->resourceConnection->getTableName('catalog_product_entity'); //gives table name with prefix
                $connection->update(
                    $tableName,
                    ['updated_at' => date('Y-m-d H:i:s')],
                    $connection->quoteInto("entity_id = ?", $productId)
                );
            } catch (\Exception $e) {
                $productId = $product->getId();
            }
        }

        if ($product->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED
            || $product->getData('webpos_visible') != \Magestore\Webpos\Model\ResourceModel\Catalog\Product\Collection::VISIBLE_ON_WEBPOS
        ) {
            $productDeleted = $this->productDeletedFactory->create()->load($productId, 'product_id');
            if (!$productDeleted->getId()) {
                $this->productDeletedInterface->setProductId($productId);
                $this->productDeletedRepository->save($this->productDeletedInterface);
            }
        } else if ($product->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
            && $product->getData('webpos_visible') == \Magestore\Webpos\Model\ResourceModel\Catalog\Product\Collection::VISIBLE_ON_WEBPOS
        ) {
            $productDeleted = $this->productDeletedFactory->create()->load($productId, 'product_id');
            if ($productDeleted->getId()) {
                $this->productDeletedResource->delete($productDeleted);
            }
        }
        if (!$product->isComposite()) {
            $parentIds = [];
            foreach ($this->getProductTypeInstances() as $typeInstance) {
                /* @var $typeInstance \Magento\Catalog\Model\Product\Type\AbstractType */
                $parentIds = array_merge($parentIds, $typeInstance->getParentIdsByChild($productId));
            }
            if(!empty($parentIds)) {
                $productCollection = $product->getCollection()->addIdFilter($parentIds);
                foreach ($productCollection as $parent) {
                    $parent->setUpdatedAt(date("Y-m-d H:i:s"));
                    $parent->getResource()->save($parent);
                }
            }
        }
    }

    /**
     * Retrieve Product Type Instances
     * as key - type code, value - instance model
     *
     * @return array
     */
    protected function getProductTypeInstances()
    {
        if (empty($this->productTypes)) {
            $productEmulator = new \Magento\Framework\DataObject();
            foreach (array_keys($this->productType->getTypes()) as $typeId) {
                $productEmulator->setTypeId($typeId);
                $this->productTypes[$typeId] = $this->productType->factory($productEmulator);
            }
        }
        return $this->productTypes;
    }
}