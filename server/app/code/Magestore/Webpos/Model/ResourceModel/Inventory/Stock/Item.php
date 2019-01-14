<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Model\ResourceModel\Inventory\Stock;

use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use \Magento\CatalogInventory\Model\Stock;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

/**
 * Stock item resource model
 */
class Item extends \Magento\CatalogInventory\Model\ResourceModel\Stock\Item
{

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Processor $processor,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfigurationInterface,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        $connectionName = null
    )
    {
        parent::__construct($context, $processor, $connectionName);
        $this->stockConfiguration = $stockConfigurationInterface;
        $this->productMetadata = $productMetadata;
        $this->countryFactory = $countryFactory;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function addStockDataToCollection($collection)
    {
        $websiteId = $this->stockConfiguration->getDefaultScopeId();

        $joinCondition = $this->getConnection()->quoteInto(
            'e.entity_id = stock_item_index.product_id',
            $websiteId
        );

        $collection->getSelect()->join(
            ['stock_item_index' => $this->getMainTable()],
            $joinCondition,
            ['item_id' => 'item_id',
                'stock_id' => 'stock_id',
                'product_id' => 'product_id',
                'qty' => 'qty',
                'is_in_stock' => 'is_in_stock',
                'manage_stock' => 'manage_stock',
                'use_config_manage_stock' => 'use_config_manage_stock',
                'backorders' => 'backorders',
                'use_config_backorders' => 'use_config_backorders',
                'min_qty' => 'min_qty',
                'use_config_min_qty' => 'use_config_min_qty',
                'min_sale_qty' => 'min_sale_qty',
                'use_config_min_sale_qty' => 'use_config_min_sale_qty',
                'max_sale_qty' => 'max_sale_qty',
                'use_config_max_sale_qty' => 'use_config_max_sale_qty',
                'is_qty_decimal' => 'is_qty_decimal',
                'use_config_qty_increments' => 'use_config_qty_increments',
                'qty_increments' => 'qty_increments',
                'use_config_enable_qty_inc' => 'use_config_enable_qty_inc',
                'enable_qty_increments' => 'enable_qty_increments',
//                'updated_time' => 'updated_time',
            ]
        );
        $collection->getSelect()->join(
            ['ea' => $this->getTable('eav_attribute')],
            "ea.entity_type_id = 4 AND ea.attribute_code = 'name'",
            [
                'name_attribute_id' => 'attribute_id'
            ]
        );

        if (!$this->isMagentoEnterprise()) {
            $collection->getSelect()->join(
                ['cpev' => $this->getTable('catalog_product_entity_varchar')],
                "cpev.entity_id = e.entity_id AND cpev.attribute_id = ea.attribute_id",
                [
                    'name' => 'value'
                ]
            );
        } else {
            $collection->getSelect()->join(
                ['cpev' => $this->getTable('catalog_product_entity_varchar')],
                "cpev.row_id = e.row_id AND cpev.attribute_id = ea.attribute_id",
                [
                    'name' => 'value'
                ]
            );
        }

        return $collection;
    }

    /**
     * @return bool
     */
    public function isMagentoEnterprise()
    {
        $edition = $this->productMetadata->getEdition();
        return  $edition == 'Enterprise' || $edition == 'B2B';
    }

    /**
     * @param $product_id
     * @param int $website_id
     * @return array
     */
    public function getAvailableQty($product_id, $website_id = 0) {
        $connection = $this->getConnection();

        $select = $connection->select();
        $select->from(['e' => $this->getTable('cataloginventory_stock_item')]);
        $select->where('product_id = ' . $product_id);
        $select->where('website_id = '.$website_id);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->columns("qty");

        $qtys = $connection->fetchAll($select);

        return $qtys;
    }

    /**
     * @param $product_id
     * @return array
     */
    public function getExternalStock($product_id, $location_id) {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from(['e' => $this->getTable('cataloginventory_stock_item')]);
        $select->where('product_id = ' . $product_id);
        $select->where('website_id != 0');
//        $select->where('website_id != '.$location_id);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->joinLeft(['location' => $this->getTable('os_warehouse')],
            'e.website_id = location.warehouse_id',
            [
                'name'=>'location.warehouse_name',
//                'address' => new \Zend_Db_Expr("CONCAT(location.street, ', ', location.city, ', ', location.country_id, ', ', location.postcode)"),
                'street' => 'location.street',
                'city' => 'location.city',
                'country_id' => 'location.country_id',
                'postcode' => 'location.postcode',
                'is_current_location' => new \Zend_Db_Expr('IF(location.warehouse_id = '.$location_id.', 1,0)'),
                'is_in_stock' => 'e.is_in_stock',
                'use_config_manage_stock' => 'e.use_config_manage_stock',
                'manage_stock' => 'e.manage_stock'
            ]);
        $select->columns(['qty']);
        $select->order('e.qty DESC');
        $qtys = $connection->fetchAll($select);

        $countryNames = [];
        foreach ($qtys as $key => $_qty){
            if (!isset($countryNames[$_qty['country_id']])){
                $countryModel = $this->countryFactory->create()->loadByCode($_qty['country_id']);
                $countryNames[$_qty['country_id']] = $countryModel->getName();
            }
            $qtys[$key]['address'] = $_qty['street'].', '.$_qty['city'].', '.$countryNames[$_qty['country_id']].', '.$_qty['postcode'];
            $qtys[$key]['qty'] = round($_qty['qty'], 4);
            unset($qtys[$key]['street']);
            unset($qtys[$key]['city']);
            unset($qtys[$key]['country_id']);
            unset($qtys[$key]['postcode']);
        }
        unset($countryNames);
        return $qtys;
    }
    
}