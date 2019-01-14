<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\Inventory\Stock;


/**
 * Class StockItemRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExternalStock extends \Magento\Framework\DataObject
    implements \Magestore\Webpos\Api\Data\Inventory\ExternalStockInterface
{
    /**
     * @inheritdoc
     */
    public function getQty() {
        return $this->getData(self::QTY);
    }

    /**
     * @inheritdoc
     */
    public function setQty($Qty) {
        return $this->setData(self::QTY, $Qty);
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name) {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getAddress() {
        return $this->getData(self::ADDRESS);
    }

    /**
     * @inheritdoc
     */
    public function setAddress($address) {
        return $this->setData(self::ADDRESS, $address);
    }
}
