<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Model\Catalog;


/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CategoryRepository extends \Magento\Catalog\Model\CategoryRepository
    implements \Magestore\Webpos\Api\Catalog\CategoryRepositoryInterface
{
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Tree
     */
    protected $treeCategory;

    /**
     * @var \Magestore\Webpos\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Block\Adminhtml\Category\Tree $treeCategory,
        \Magestore\Webpos\Helper\Data $helper
    )
    {
        parent::__construct($categoryFactory, $categoryResource, $storeManager);
        $this->treeCategory = $treeCategory;
        $this->helper = $helper;
    }

    /**
     * Get category list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magestore\Webpos\Api\Data\Catalog\CategorySearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $store = $this->helper->getCurrentStoreView();
        $storeId = $store->getId();
        $rootCategory = $store->getRootCategoryId();
        $collection = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\Webpos\Model\ResourceModel\Catalog\Category\Collection'
        );
        $isShowFirstCats = false;
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $fields = [];
            foreach ($group->getFilters() as $filter) {
                $conditionType = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                if ($filter->getField() == 'first_category') {
                    $isShowFirstCats = true;
                    continue;
                }
                $fields[] = ['attribute' => $filter->getField(), $conditionType => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields);
            }
        }
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders === null) {
            $sortOrders = [];
        }
        /** @var \Magento\Framework\Api\SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $direction = ($sortOrder->getDirection() == 'ASC') ? 'ASC' : 'DESC';
            $collection->addAttributeToSort($field, $direction);
        }
        if ($isShowFirstCats) {
            $collection->addFieldToFilter('parent_id', $rootCategory);
        }
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('image');
        $collection->addAttributeToSelect('path');
        $collection->addAttributeToSelect('parent_id');
        $collection->addAttributeToSelect('is_active');
        $collection->addAttributeToFilter(\Magento\Catalog\Model\Category::KEY_IS_ACTIVE, '1');
        $collection->setStoreId($storeId);
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();
        $searchResult = \Magento\Framework\App\ObjectManager::getInstance()->get(
            '\Magestore\Webpos\Api\Data\Catalog\CategorySearchResultsInterface'
        );
        $searchResult->setSearchCriteria($searchCriteria);
        $items = $this->verifyCategories($collection->getItems(), $isShowFirstCats);
        $searchResult->setItems($items);
        $searchResult->setTotalCount(count($items));
        return $searchResult;
    }

    /**
     * @param $categories
     * @return array
     */
    protected function verifyCategories($categories)
    {

        $tmp_categories = [];
        foreach ($categories as $category) {
            $tmp_categories[$category->getId()] = $category;
        }
        $categories = $tmp_categories;
        $categoryIds = array_keys($categories);

        $treeCategories = $this->treeCategory->getTree();
        $this->verifyTree($treeCategories, $categories);
        $tree = $this->getTreeId($treeCategories, $categories, $categoryIds);
        return $tree;
    }

    /**
     * Fix bug cannot get categories from level 5 and greater
     * Verify to add children categories above level 5
     * @param array $tree
     * @param array $categories
     */
    protected function verifyTree(&$tree, $categories)
    {
        foreach ($tree as &$item) {
            if (isset($item['children'])) {
                if (count($item['children'])) {
                    $this->verifyTree($item['children'], $categories);
                } else {
                    $this->addChildrenCategoriesManually($item, $categories);
                }
            }
        }
    }

    /**
     * Add sub categories manually
     * @param array $category
     * @param array $categories
     */
    protected function addChildrenCategoriesManually(&$category, $categories)
    {
        /** @var \Magento\Catalog\Model\Category $parent */
        $parent = $this->categoryFactory->create()->load($category['id']);
        $children = [];
        foreach ($parent->getChildrenCategories() as $childCategory) {
            if (!isset($categories[$childCategory->getId()]))
                continue;
            $data = $categories[$childCategory->getId()]->getData();
            // change field for recursive call
            $data['id'] = $data['entity_id'];
            if (isset($data['children_count']) && $data['children_count']) {
                // add element children when category has children
                $data['children'] = [];
            }
            // add category instead of category tree
            // because this function only need "id" and "children" of tree
            $children[] = $data;
        }

        if (count($children)) {
            $category['children'] = $children;
            // verify tree if this category have children
            $this->verifyTree($category['children'], $categories);
        } else {
            unset($category['children']);
        }
    }

    protected function getTreeId($tree, $categories, $categoryIds)
    {
        $data = [];
        foreach ($tree as $item) {
            if (!in_array($item['id'], $categoryIds)) {
                continue;
            }

            if (isset($item['children'])) {
                $data[$item['id']] = $categories[$item['id']]->setChildren($this->getTreeId($item['children'], $categories, $categoryIds));
            } else {
                $data[$item['id']] = $categories[$item['id']];
            }
        }
        return $data;
    }
}
