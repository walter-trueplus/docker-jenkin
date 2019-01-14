<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\Inventory;

use \Magento\Framework\ObjectManagerInterface as ObjectManagerInterface;
use Magestore\Webpos\Model\Location\LocationFactory;
use \Magestore\Webpos\Model\ResourceModel\Inventory\Stock\Item as StockItemResource;
use \Magento\CatalogInventory\Model\Stock\Item as StockItemModel;
use \Magento\CatalogInventory\Api\StockItemRepositoryInterface as CoreStockItemRepositoryInterface;
use \Magento\Framework\Api\SortOrder;
use Magestore\Webpos\Api\Inventory\StockItemRepositoryInterface;
use \Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Framework\Exception\AuthorizationException;
use \Magento\CatalogInventory\Model\Stock as Stock;

/**
 * Class StockItemRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockItemRepository implements StockItemRepositoryInterface
{

    /**
     *
     * @var \Magestore\Webpos\Model\ResourceModel\Inventory\Stock\Item
     */
    protected $resource;

    /**
     *
     * @var \Magento\CatalogInventory\Model\Stock\Item
     */
    protected $stockItemModel;

    /**
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     *
     * @var CoreStockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;
    /**
     *
     * @var StockRegistryProviderInterface
     */
    protected $stockRegistryProvider;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var array
     */
    protected $listCondition = [
        'eq' => '=',
        'neq' => '!=',
        'like' => 'like',
        'gt' => '>',
        'gteq' => '>=',
        'lt' => '<',
        'lteq' => '<=',
        'in' => 'in'
    ];

    /**
     * @var \Magestore\Webpos\Model\ResourceModel\Catalog\Product\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Api\Search\SearchResultFactory
     */
    protected $_searchResultFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magestore\Webpos\Api\Data\Inventory\AvailableQtyInterface
     */
    protected $availableQty;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var LocationFactory
     */
    protected $locationFactory;

    /**
     * @var \Magestore\Appadmin\Api\Staff\RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var \Magestore\Webpos\Helper\Data
     */
    protected $helper;

    /**
     * StockItemRepository constructor.
     * @param StockItemResource $resource
     * @param StockItemModel $stockItemModel
     * @param CoreStockItemRepositoryInterface $stockItemRepository
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magestore\Webpos\Model\ResourceModel\Catalog\Product\CollectionFactory $collectionFactyory
     * @param \Magento\Framework\Api\Search\SearchResultFactory $searchResultFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magestore\Webpos\Api\Data\Inventory\AvailableQtyInterface $availableQty
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        StockItemResource $resource,
        StockItemModel $stockItemModel,
        CoreStockItemRepositoryInterface $stockItemRepository,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        StockRegistryProviderInterface $stockRegistryProvider,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magestore\Webpos\Model\ResourceModel\Catalog\Product\CollectionFactory $collectionFactyory,
        \Magento\Framework\Api\Search\SearchResultFactory $searchResultFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magestore\Webpos\Api\Data\Inventory\AvailableQtyInterface $availableQty,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magestore\Webpos\Model\Location\LocationFactory $locationFactory,
        \Magestore\Appadmin\Api\Staff\RuleRepositoryInterface $ruleRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Magestore\Webpos\Helper\Data $helper
    )
    {
        $this->resource = $resource;
        $this->stockItemModel = $stockItemModel;
        $this->stockItemRepository = $stockItemRepository;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->eventManager = $eventManager;
        $this->_collectionFactory = $collectionFactyory;
        $this->_searchResultFactory = $searchResultFactory;
        $this->_moduleManager = $moduleManager;
        $this->availableQty = $availableQty;
        $this->productFactory = $productFactory;
        $this->coreRegistry = $coreRegistry;
        $this->locationFactory = $locationFactory;
        $this->ruleRepository = $ruleRepository;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function getStockItems(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->_collectionFactory->create();
        $storeId = $this->helper->getCurrentStoreView()->getId();
        $collection->addAttributeToSelect('name');
        $collection->getSelect()->group('e.entity_id');
        $collection->setStoreId($storeId);
        if (!$this->_moduleManager->isEnabled('Magestore_InventorySuccess')) {
            $collection->addStoreFilter($storeId);
        }
        $collection->addAttributeToFilter('type_id', ['in' => $this->getProductTypeIds()]);
        $collection = $this->resource->addStockDataToCollection($collection);
        /** Integrate webpos **/
        $warehouse_id = 0;
        $sessionModel = $this->coreRegistry->registry('currrent_session_model');
        if ($sessionModel && $sessionModel->getLocationId()) {
            $warehouse_id = $sessionModel->getLocationId();
        }
        $this->eventManager->dispatch('webpos_inventory_stockitem_getstockitems', [
            'collection' => $collection,
            'warehouse_id' => $warehouse_id
        ]);
        /** End integrate webpos **/
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field, ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? SortOrder::SORT_ASC : SortOrder::SORT_DESC
            );
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();
        $searchResult = $this->_searchResultFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * {@inheritDoc}
     */
    public function sync(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Framework\App\Cache\StateInterface $cacheState */
        $cacheState = $objectManager->get('Magento\Framework\App\Cache\StateInterface');
        if (!$cacheState->isEnabled(\Magestore\Webpos\Model\Cache\Type::TYPE_IDENTIFIER)
            || count($searchCriteria->getFilterGroups())
        ) {
            return $this->getStockItems($searchCriteria);
        }

        /** @var \Magento\Framework\App\CacheInterface $cache */
        $cache = $objectManager->get('Magento\Framework\App\CacheInterface');
        /** @var \Magento\Framework\Serialize\SerializerInterface $serializer */
        /*$serializer = $objectManager->get('Magento\Framework\Serialize\SerializerInterface');*/

        /** @var \Magestore\Webpos\Api\Staff\SessionRepositoryInterface $sessionRepository */
        $sessionRepository = $objectManager->get('\Magestore\Webpos\Api\Staff\SessionRepositoryInterface');
        $request = $objectManager->get('Magento\Framework\App\RequestInterface');
        $session = $sessionRepository->getBySessionId($request->getParam(\Magestore\WebposIntegration\Controller\Rest\RequestProcessor::SESSION_PARAM_KEY));
        $locationId = $session->getLocationId();

        if (!$this->_moduleManager->isEnabled('Magestore_InventorySuccess')) {
            $locationId = Stock::DEFAULT_STOCK_ID;
        }

        $key = 'syncStockItems-' . $searchCriteria->getPageSize() . '-' . $searchCriteria->getCurrentPage() . '-' . $locationId;

        if ($response = $cache->load($key)) {
            // Reponse from cache
//            return $serializer->unserialize($response);
            return  json_decode($response, true);
        }

        // Check async queue for product
        $flag = \Magestore\Webpos\Api\SyncInterface::QUEUE_NAME;
        if ($cachedAt = $cache->load($flag)) {
            return [
                'async_stage'   => 2, // processing
                'cached_at'     => $cachedAt, // process started time
            ];
        }

        // Block metaphor
        $cachedAt = date("Y-m-d H:i:s");
        $cache->save($cachedAt, $flag, [\Magestore\Webpos\Model\Cache\Type::CACHE_TAG], 300);

        /** @var \Magento\Framework\Webapi\ServiceOutputProcessor $processor */
        $processor = $objectManager->get('Magento\Framework\Webapi\ServiceOutputProcessor');
        $outputData = $processor->process(
            $this->getStockItems($searchCriteria),
            'Magestore\Webpos\Api\Inventory\StockItemRepositoryInterface',
            'sync'
        );
        $outputData['cached_at'] = $cachedAt;
        $cache->save(
//            $serializer->serialize($outputData),
            json_encode($outputData),
            $key,
            [
                \Magestore\Webpos\Model\Cache\Type::CACHE_TAG,
            ],
            3600    // Cache lifetime: 1 hour
        );
        // Release metaphor
        $cache->remove($flag);
        return $outputData;
    }

    /**
     * get product type ids to support
     * @return array
     */
    public function getProductTypeIds()
    {
        $types = [
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE,
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        ];
        if ($this->helper->isEnabledGiftCard()){
            $types[] = \Magestore\Giftvoucher\Model\Product\Type\Giftvoucher::GIFT_CARD_TYPE;
        }
        return $types;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup, \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    )
    {
        $where = '(';
        $first = true;
        foreach ($filterGroup->getFilters() as $filter) {
            $conditionType = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $condition = $this->convertCondition($conditionType);
            $value = is_array($filter->getValue()) ? "('" . implode("','", $filter->getValue()) . "')" : $filter->getValue();

            if (in_array($condition, ['IN', 'NOT IN'])) {
                $value = '(' . $value . ')';
            } else {
                $value = "'" . $value . "'";
            }


            if (!$first) {
                $where .= ' OR ';
            }
            $where .= $filter->getField() . " " . $condition . ' ' . $value;
            $first = false;
        }

        $where .= ')';
        $collection->getSelect()->where($where);
    }

    /**
     * Convert sql condition from Magento to Zend Db Select
     *
     * @param string $type
     * @return string
     */
    protected function convertCondition($type)
    {
        switch ($type) {
            case 'gt':
                return '>';
            case 'gteq':
                return '>=';
            case 'lt':
                return '<';
            case 'lteq':
                return '=<';
            case 'eq':
                return '=';
            case 'in':
                return 'IN';
            case 'nin':
                return 'NOT IN';
            case 'neq':
                return '!=';
            case 'like':
                return 'LIKE';
            default:
                return '=';
        }
    }

    /**
     *
     * @param array $stockItems
     * @return bool
     */
    public function massUpdateStockItems($stockItems)
    {
        if (count($stockItems)) {
            foreach ($stockItems as $stockItem) {
                if (!$stockItem->getItemId())
                    continue;
                $this->updateStockItem($stockItem->getItemId(), $stockItem);
            }
        }
        return true;
    }

    /**
     *
     * @param string $itemId
     * @param \Magestore\Webpos\Api\Data\Inventory\StockItemInterface $stockItem
     * @return int
     */
    public function updateStockItem($itemId, \Magestore\Webpos\Api\Data\Inventory\StockItemInterface $stockItem)
    {
        $origStockItem = $this->stockItemModel->load($itemId);
        $changeQty = $stockItem->getQty() - $origStockItem->getQty();
        $data = $stockItem->getData();
        if ($origStockItem->getItemId()) {
            unset($data['item_id']);
        }
        $origStockItem->addData($data);

        $stockItem = $this->stockItemRepository->save($origStockItem);

        $this->eventManager->dispatch('webpos_inventory_stockitem_update', [
            'stock_item' => $stockItem,
            'change_qty' => $changeQty,
        ]);

        return $stockItem->getItemId();
    }

    /**
     * @inheritdoc
     */
    public function getAvailableQty($product_id) {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->get('Magento\Catalog\Model\ProductFactory')->create()->load($product_id);
        if(!$product->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Product does not exist!'));
        }
        $websiteId = 0;
        if ($this->_moduleManager->isEnabled('Magestore_InventorySuccess')){
            /** @var \Magestore\Webpos\Model\Staff\Session $sessionModel */
            $sessionModel = $this->coreRegistry->registry('currrent_session_model');
            $location = $this->locationFactory->create()->load($sessionModel->getLocationId());
            if ($location->getId()){
                $websiteId = $location->getId();
            }
        }
        $qtys = $this->resource->getAvailableQty($product_id, $websiteId);

        $availableQty = $this->availableQty;

        if(!count($qtys)) {
            $availableQty->setAvailableQty(0);
        } else {
            $availableQty->setAvailableQty(round($qtys[0]['qty'], 4));
        }

        return $availableQty;
    }

    /**
     * @inheritdoc
     */
    public function getExternalStock($product_id) {
        /** @var \Magestore\Appadmin\Model\Staff\Staff $staff */
//        $staff = $this->coreRegistry->registry('currrent_webpos_staff');
//        if (!$this->ruleRepository->isAllowPermission('Magestore_Webpos::check_external_stock', $staff->getStaffId())) {
//            throw new AuthorizationException(
//                __(\Magestore\Appadmin\Api\Event\DispatchServiceInterface::EXCEPTION_VEW_EXTERNAL_STOCK_MESSAGE),
//                new \Exception(),
//                \Magestore\Appadmin\Api\Event\DispatchServiceInterface::EXCEPTION_CODE_EXTERNAL_STOCKS
//            );
//        }

        /** @var \Magestore\Webpos\Model\Staff\Session $sessionModel */
        $sessionModel = $this->coreRegistry->registry('currrent_session_model');
        $location = $this->locationFactory->create()->load($sessionModel->getLocationId());
        if (!$location->getId()){
            throw new AuthorizationException(
                __('You did not select location'),
                new \Exception(),
                \Magestore\Appadmin\Api\Event\DispatchServiceInterface::EXCEPTION_CODE_FORCE_CHANGE_POS
            );
        }

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productFactory->create()->load($product_id);
        if(!$product->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Product does not exist!'));
        }

        $qtys = $this->resource->getExternalStock($product_id, $location->getId());
        return $qtys;
    }
}
