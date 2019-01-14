<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Api\Data\Staff\Login;

/**
 * @api
 */
/**
 * Interface LocationResultInterface
 * @package Magestore\Webpos\Api\Data\Staff\Login
 */
interface LocationResultInterface
{
    const LOCATION_ID = 'location_id';
    const LOCATION_NAME = 'location_name';
    const LOCATION_CODE = 'warehouse_code';
    const STORE_ID = 'store_id';
    const TELEPHONE = 'telephone';
    const ADDRESS = 'address';
    const POS = 'pos';

    /**
     * Get location id
     *
     * @api
     * @return int
     */
    public function getLocationId();

    /**
     * Set location id
     *
     * @api
     * @param int $locationId
     * @return LocationResultInterface
     */
    public function setLocationId($locationId);
    /**
     * Get location name
     *
     * @api
     * @return string
     */
    public function getName();

    /**
     * Set location name
     *
     * @api
     * @param string $locationName
     * @return LocationResultInterface
     */
    public function setName($locationName);
    /**
     * Get location code
     *
     * @api
     * @return string
     */
    public function getLocationCode();

    /**
     * Set location code
     *
     * @api
     * @param string $locationCode
     * @return LocationResultInterface
     */
    public function setLocationCode($locationCode);
    /**
     * Get address
     * @return \Magestore\Webpos\Api\Data\Staff\Login\Location\AddressInterface
     */
    public function getAddress();

    /**
     * Set address
     * @param \Magestore\Webpos\Api\Data\Staff\Login\Location\AddressInterface $address
     * @return LocationResultInterface
     */
    public function setAddress($address);
    /**
     * Get telephone
     * @return string
     */
    public function getTelephone();

    /**
     * Set telephone
     * @param string $telephone
     * @return LocationResultInterface
     */
    public function setTelephone($telephone);
    /**
     * Get store id
     *
     * @api
     * @return int
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @api
     * @param int $storeId
     * @return LocationResultInterface
     */
    public function setStoreId($storeId);
    /**
     * Get pos
     *
     * @api
     * @return \Magestore\Webpos\Api\Data\Staff\Login\PosResultInterface[]
     */
    public function getPos();
    /**
     * Set pos
     *
     * @api
     * @param \Magestore\Webpos\Api\Data\Staff\Login\PosResultInterface $pos
     * @return LocationResultInterface
     */
    public function setPos($pos);
}
