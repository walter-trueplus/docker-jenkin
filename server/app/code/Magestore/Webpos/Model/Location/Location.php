<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\Location;

use Magestore\Webpos\Api\Data\Location\LocationInterface;

/**
 * Class Location
 * @package Magestore\Webpos\Model\Location
 */
class Location extends \Magento\Framework\Model\AbstractModel implements \Magestore\Webpos\Api\Data\Location\LocationInterface
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webpos_location';
    /**
     * @var \Magento\Directory\Model\Country
     */
    protected $country;
    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;
    /**
     * @var \Magestore\Webpos\Api\Data\Staff\Login\Location\AddressInterface
     */
    protected $addressInterface;
    /**
     * @var \Magento\Customer\Api\Data\RegionInterface
     */
    protected $region;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Location constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magestore\Webpos\Model\ResourceModel\Location\Location $resource
     * @param \Magestore\Webpos\Model\ResourceModel\Location\Location\Collection $resourceCollection
     * @param \Magento\Directory\Model\Country $country
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magestore\Webpos\Api\Data\Staff\Login\Location\AddressInterface $addressInterface
     * @param \Magento\Customer\Api\Data\RegionInterface $region
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\Webpos\Model\ResourceModel\Location\Location $resource,
        \Magestore\Webpos\Model\ResourceModel\Location\Location\Collection $resourceCollection,
        \Magento\Directory\Model\Country $country,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magestore\Webpos\Api\Data\Staff\Login\Location\AddressInterface $addressInterface,
        \Magento\Customer\Api\Data\RegionInterface $region,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = [])
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->country = $country;
        $this->regionFactory = $regionFactory;
        $this->addressInterface = $addressInterface;
        $this->region = $region;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @inheritdoc
     */
    public function getLocationId()
    {
        if ($this->moduleManager->isEnabled('Magestore_InventorySuccess')) {
            return $this->getData(self::WAREHOUSE_ID);
        } else {
            return $this->getData(self::LOCATION_ID);
        }
    }

    /**
     * @inheritdoc
     */
    public function setLocationId($locationId)
    {
        if ($this->moduleManager->isEnabled('Magestore_InventorySuccess')) {
            return $this->setData(self::WAREHOUSE_ID, $locationId);
        } else {
            return $this->setData(self::LOCATION_ID, $locationId);
        }
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        if ($this->moduleManager->isEnabled('Magestore_InventorySuccess')) {
            return $this->getData(self::WAREHOUSE_ID);
        } else {
            return $this->getData(self::LOCATION_ID);
        }
    }

    /**
     * @inheritdoc
     */
    public function setId($locationId)
    {
        if ($this->moduleManager->isEnabled('Magestore_InventorySuccess')) {
            return $this->setData(self::WAREHOUSE_ID, $locationId);
        } else {
            return $this->setData(self::LOCATION_ID, $locationId);
        }
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        if ($this->moduleManager->isEnabled('Magestore_InventorySuccess')) {
            return $this->getData(self::WAREHOUSE_NAME);
        } else {
            return $this->getData(self::NAME);
        }
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        if ($this->moduleManager->isEnabled('Magestore_InventorySuccess')) {
            return $this->setData(self::WAREHOUSE_NAME, $name);
        } else {
            return $this->setData(self::NAME, $name);
        }
    }

    /**
     * Get location code
     *
     * @api
     * @return string
     */
    public function getLocationCode()
    {
        return $this->getData(self::LOCATION_CODE);
    }

    /**
     * Set location code
     *
     * @api
     * @param string $locationCode
     * @return $this
     */
    public function setLocationCode($locationCode)
    {
        return $this->setData(self::LOCATION_CODE, $locationCode);
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function getTelephone()
    {
        if ($telephone = $this->getData(self::TELEPHONE)) {
            return $telephone;
        } else {
            return "";
        }
    }

    /**
     * @inheritdoc
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    /**
     * @inheritdoc
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @inheritdoc
     */
    public function getStreet()
    {
        return $this->getData(self::STREET);
    }

    /**
     * @inheritdoc
     */
    public function setStreet($street)
    {
        return $this->setData(self::STREET, $street);
    }

    /**
     * @inheritdoc
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * @inheritdoc
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @inheritdoc
     */
    public function getRegion()
    {
        return $this->getData(self::REGION);
    }

    /**
     * @inheritdoc
     */
    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * @inheritdoc
     */
    public function getRegionId()
    {
        return $this->getData(self::REGION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * @inheritdoc
     */
    public function getCountry()
    {
        return $this->getData(self::COUNTRY);
    }

    /**
     * @inheritdoc
     */
    public function setCountry($country)
    {
        return $this->setData(self::COUNTRY, $country);
    }

    /**
     * @inheritdoc
     */
    public function getCountryId()
    {
        return $this->getData(self::COUNTRY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCountryId($countryId)
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }

    /**
     * @inheritdoc
     */
    public function getPostcode()
    {
        return $this->getData(self::POSTCODE);
    }

    /**
     * @inheritdoc
     */
    public function setPostcode($postcode)
    {
        return $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * @inheritdoc
     */
    public function getAddress()
    {
        $street = $this->getStreet();
        $city = $this->getCity();
        $region = $this->getRegion();
        $regionId = $this->getRegionId();
        $country = $this->getCountry();
        $countryId = $this->getCountryId();
        $postcode = $this->getPostcode();

        $regionCode = $region;
        if ($regionId) {
            $regionCode = $this->regionFactory->create()->load($regionId)->getCode();
        }

        $regionObject = $this->region;
        $regionObject->setRegion($region);
        $regionObject->setRegionCode($regionCode);
        $regionObject->setRegionId($regionId);

        $address = [
            'street' => $street,
            'city' => $city,
            'region' => $regionObject,
            'region_id' => $regionId,
            'country_id' => $countryId,
            'country' => $country,
            'postcode' => $postcode,
        ];
        return $this->addressInterface->setData($address);
    }

    /**
     * @inheritdoc
     */
    public function setAddress($address)
    {
        return $this->getData('address', $address);
    }

    public function getWarehouseId()
    {
        return $this->getData(self::WAREHOUSE_ID);
    }

    public function setWarehouseId($warehouseId)
    {
        return $this->setData(self::WAREHOUSE_ID, $warehouseId);
    }

    /**
     * Get Stock Id
     *
     * @return int|null
     */
    public function getStockId()
    {
        return $this->getData(self::STOCK_ID);
    }

    /**
     * Set Stock Id
     *
     * @param int|null $stockId
     * @return LocationInterface
     */
    public function setStockId($stockId)
    {
        return $this->setData(self::STOCK_ID, $stockId);
    }


    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        if ($regionId = $this->getRegionId()) {
            $rgModel = $this->regionFactory->create()->load($regionId);
            if ($rgModel->getId()) {
                $this->setRegion($rgModel->getName());
            }
        }

        $countryId = $this->getCountryId();
        $countryModel = $this->country->loadByCode($countryId);
        $this->setCountry($countryModel->getName());

        return parent::beforeSave();
    }
}