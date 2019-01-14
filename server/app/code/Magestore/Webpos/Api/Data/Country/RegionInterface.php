<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Api\Data\Country;

/**
 * Region interface.
 */
interface RegionInterface
{
    const ID = 'id';
    const CODE = 'code';
    const NAME = 'name';
    /**
     * Get ID
     *
     * @api
     * @return int
     */
    public function getId();

    /**
     * Set ID
     *
     * @api
     * @param int $id
     * @return RegionInterface
     */
    public function setId($id);
    /**
     * Get code
     *
     * @api
     * @return string
     */
    public function getCode();

    /**
     * Set code
     *
     * @api
     * @param string $code
     * @return RegionInterface
     */
    public function setCode($code);
    /**
     * Get name
     *
     * @api
     * @return string
     */
    public function getName();

    /**
     * Set name
     *
     * @api
     * @param string $name
     * @return RegionInterface
     */
    public function setName($name);
}
