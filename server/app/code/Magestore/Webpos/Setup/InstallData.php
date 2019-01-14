<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package Magestore\Webpos\Setup
 */
class InstallData implements InstallDataInterface
{

    /**
     *
     */
    const LOCATION_NAME = 'Primary Location';
    /**
     *
     */
    const NOT_ENCODE_PASSWORD = 1;

    /**
     * @var \Magestore\Webpos\Model\Location\LocationFactory
     */
    protected $_locationFactory;

    /**
     * @var \Magestore\Webpos\Model\Pos\PosFactory
     */
    protected $_posFactory;

    /**
     * @var \Magestore\Webpos\Model\ResourceModel\Location\Location\CollectionFactory
     */
    protected $_locationCollectionFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;


    /**
     * InstallData constructor.
     * @param \Magestore\Webpos\Model\Location\LocationFactory $locationFactory
     * @param \Magestore\Webpos\Model\Pos\PosFactory $posFactory
     */
    public function __construct(
        \Magestore\Webpos\Model\Location\LocationFactory $locationFactory,
        \Magestore\Webpos\Model\Pos\PosFactory $posFactory,
        \Magestore\Webpos\Model\ResourceModel\Location\Location\CollectionFactory $locationCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Directory\Model\CountryFactory $countryFactory
    )
    {
        $this->_locationFactory = $locationFactory;
        $this->_posFactory = $posFactory;
        $this->_locationCollectionFactory = $locationCollectionFactory;
        $this->moduleManager = $moduleManager;
        $this->countryFactory = $countryFactory;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $isMSI = $this->moduleManager->isEnabled('Magento_Inventory') &&
            $this->moduleManager->isEnabled('Magento_InventoryApi');

        $locationData = null;

        /*
         * Setup Location Data
         */
        if (!$this->moduleManager->isEnabled('Magestore_InventorySuccess')) {
            $locationData = array(
                'name' => 'Primary Location',
                'street' => '6146 Honey Bluff Parkway',
                'city' => 'Calder',
                'region' => 'Michigan',
                'region_id' => 33,
                'country_id' => 'US',
                'country' => 'United State',
                'postcode' => '49628-7978',
                'description' => 'To distribute products for brick-and-mortar store'
            );
        }
        if ($isMSI) {
            /** @var \Magento\Framework\App\ObjectManager $objectManager */
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Magento\InventoryApi\Api\SourceRepositoryInterface $sourceRepository */
            $sourceRepository = $objectManager->create('Magento\InventoryApi\Api\SourceRepositoryInterface');
            /** @var \Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface $defaultSourceProvider */
            $defaultSourceProvider = $objectManager->create('Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface');
            /** @var \Magento\InventoryApi\Api\Data\SourceInterface $defaultSource */
            $defaultSource = $sourceRepository->get($defaultSourceProvider->getCode());
            /** @var \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface $defaultStockProvider */
            $defaultStockProvider = $objectManager->create('Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface');
            if ($defaultSource->getSourceCode()) {
                /** @var \Magento\Directory\Model\Country $country */
                $country = $this->countryFactory->create();
                $country->loadByCode($defaultSource->getCountryId());
                $locationData = [
                    'name' => 'Primary Location',
                    'street' => $defaultSource->getStreet(),
                    'city' => $defaultSource->getCity(),
                    'region' => $defaultSource->getRegion(),
                    'region_id' => $defaultSource->getRegionId(),
                    'country_id' => $defaultSource->getCountryId(),
                    'country' => $country->getName(),
                    'postcode' => $defaultSource->getPostcode(),
                    'stock_id' => $defaultStockProvider->getId(),
                    'description' => 'To distribute products for brick-and-mortar store'
                ];
            }
        }

        if ($locationData) {
            $this->_locationFactory->create()->setData($locationData)->save();
        }

        /** @var \Magestore\Webpos\Model\Location\Location $locationModel */
        $locationModel = $this->_locationCollectionFactory->create()
//            ->addFieldToFilter('name', self::LOCATION_NAME)
            ->getFirstItem();
        $posData = array(
            'pos_name' => 'Primary POS',
            'location_id' => $locationModel->getId(),
            'status' => '1'
        );
        $this->_posFactory->create()->setData($posData)->save();


        $setup->endSetup();
    }
}