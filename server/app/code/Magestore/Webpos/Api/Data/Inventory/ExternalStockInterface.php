<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Api\Data\Inventory;

/**
 * @api
 */
interface ExternalStockInterface
{
    const QTY = 'qty';
    const NAME = 'name';
    const ADDRESS = 'address';

    /**
     * @return float
     */
    public function getQty();

    /**
     * @param float $Qty
     * @return ExternalStockInterface
     */
    public function setQty($Qty);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return ExternalStockInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getAddress();

    /**
     * @param string $address
     * @return ExternalStockInterface
     */
    public function setAddress($address);
}