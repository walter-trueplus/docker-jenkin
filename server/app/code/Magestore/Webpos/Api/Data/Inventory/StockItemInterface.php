<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Api\Data\Inventory;

/**
 * @api
 */
interface StockItemInterface
{ 
    
    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $productId
     * @return StockItemInterface
     */
    public function setProductId($productId);
    
    /**
     * @return int
     */
    public function getItemId();

    /**
     * @param int $itemId
     * @return StockItemInterface
     */
    public function setItemId($itemId);    

    /**
     * @return int
     */
    public function getStockId();

    /**
     * @param int $stockId
     * @return StockItemInterface
     */
    public function setStockId($stockId);    

    /**
     * Product SKU
     *
     * @return string|null
     */
    public function getSku();
    
    /**
     * 
     * @param string $sku
     * @return StockItemInterface
     */
    public function setSku($sku);
    
    /**
     * Product Name
     *
     * @return string|null
     */
    public function getName();    
    
    /**
     * 
     * @param string $name
     * @return StockItemInterface
     */
    public function setName($name);
   

    /**
     * @return float
     */
    public function getQty();

    /**
     * @param float $qty
     * @return StockItemInterface
     */
    public function setQty($qty);

    /**
     * Retrieve Stock Availability
     *
     * @return bool|int
     */
    public function getIsInStock();

    /**
     * Set Stock Availability
     *
     * @param bool|int $isInStock
     * @return StockItemInterface
     */
    public function setIsInStock($isInStock);
    
    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigManageStock();

    /**
     * @param bool $useConfigManageStock
     * @return StockItemInterface
     */
    public function setUseConfigManageStock($useConfigManageStock);

    /**
     * Retrieve can Manage Stock
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getManageStock();

    /**
     * @param bool $manageStock
     * @return StockItemInterface
     */
    public function setManageStock($manageStock);
    
    /**
     * Retrieve can backorder
     *
     * @return int
     */    
    public function getBackorders();
    
    /**
     * @param int $backorders
     * @return StockItemInterface
     */
    public function setBackorders($backorders);  
    
    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseConfigBackorders();

    /**
     * @param bool $useConfigBackorders
     * @return StockItemInterface
     */
    public function setUseConfigBackorders($useConfigBackorders);

    /**
     *
     * @return int|float
     */
    public function getMinQty();

    /**
     * @param int|float $minQty
     * @return StockItemInterface
     */
    public function setMinQty($minQty);

    /**
     * @return bool
     */
    public function getUseConfigMinQty();

    /**
     * @param bool $useConfigMinQty
     * @return StockItemInterface
     */
    public function setUseConfigMinQty($useConfigMinQty);

    /**
     *
     * @return float
     */    
    public function getMinSaleQty();
    
    /**
     * @param float $minSaleQty
     * @return StockItemInterface
     */
    public function setMinSaleQty($minSaleQty);      
    
    /**
     * @return bool
     */
    public function getUseConfigMinSaleQty();

    /**
     * @param bool $useConfigMinSaleQty
     * @return StockItemInterface
     */
    public function setUseConfigMinSaleQty($useConfigMinSaleQty);       
    
    /**
     *
     * @return float
     */    
    public function getMaxSaleQty();
    
    /**
     * @param float $maxSaleQty
     * @return StockItemInterface
     */
    public function setMaxSaleQty($maxSaleQty);         
    
    /**
     * @return bool
     */
    public function getUseConfigMaxSaleQty();

    /**
     * @param bool $useConfigMaxSaleQty
     * @return StockItemInterface
     */
    public function setUseConfigMaxSaleQty($useConfigMaxSaleQty);  
    
    /**
     * @return string
     */
    public function getUpdatedTime();

    /**
     * @param string $updatedTime
     * @return StockItemInterface
     */
    public function setUpdatedTime($updatedTime);

    /**
     * Get Use Config Qty Increments
     *
     * @return int|null
     */
    public function getUseConfigQtyIncrements();	
    /**
     * Set Use Config Qty Increments
     *
     * @param int $useConfigQtyIncrements
     * @return StockItemInterface
     */
    public function setUseConfigQtyIncrements($useConfigQtyIncrements);
    /**
     * @return string
     */
    public function getQtyIncrements();

    /**
     * @param string $qtyIncrements
     * @return StockItemInterface
     */
    public function setQtyIncrements($qtyIncrements);
    
    /**
     * @return bool
     */
    public function getIsQtyDecimal();

    /**
     * @param bool $isQtyDecimal
     * @return StockItemInterface
     */
    public function setIsQtyDecimal($isQtyDecimal);

    /**
     * Get Use Config Enable Qty Inc
     *
     * @return int|null
     */
    public function getUseConfigEnableQtyInc();	
    /**
     * Set Use Config Enable Qty Inc
     *
     * @param int $useConfigEnableQtyInc
     * @return StockItemInterface
     */
    public function setUseConfigEnableQtyInc($useConfigEnableQtyInc);

    /**
     * Get Enable Qty Increments
     *
     * @return int|null
     */
    public function getEnableQtyIncrements();	
    /**
     * Set Enable Qty Increments
     *
     * @param int $enableQtyIncrements
     * @return StockItemInterface
     */
    public function setEnableQtyIncrements($enableQtyIncrements);
}