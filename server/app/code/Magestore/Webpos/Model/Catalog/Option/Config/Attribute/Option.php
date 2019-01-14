<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Model\Catalog\Option\Config\Attribute;

/**
 * Class BundleOptionsBuilder
 * @package Magestore\Webpos\Model\Catalog\Product
 */
class Option extends \Magento\Framework\DataObject implements \Magestore\Webpos\Api\Data\Catalog\Option\Config\Attribute\OptionInterface
{
    /**
     * @inheritdoc
     */
    public function getId() {
        return $this->getData(self::ID);
    }	
    /**
     * @inheritdoc
     */
    public function setId($id) {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getLabel() {
        return $this->getData(self::LABEL);
    }	
    /**
     * @inheritdoc
     */
    public function setLabel($label) {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * @inheritdoc
     */
    public function getProducts() {
        return $this->getData(self::PRODUCTS);
    }	
    /**
     * @inheritdoc
     */
    public function setProducts($products) {
        return $this->setData(self::PRODUCTS, $products);
    }
}