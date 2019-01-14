<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Model\Source\Adminhtml;

class Stock implements \Magento\Framework\Option\ArrayInterface
{
    protected $options;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager
    )
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Return options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $isMSI = $this->moduleManager->isEnabled('Magento_Inventory') &&
            $this->moduleManager->isEnabled('Magento_InventoryApi');
        if (!$isMSI) {
            return [];
        }
        if (!$this->options) {
            $this->options = [['value' => '', 'label' => __('--Please Select--')]];
            /** @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $stockCollection */
            $stockCollection = \Magento\Framework\App\ObjectManager::getInstance()
                ->create('Magento\Inventory\Model\ResourceModel\Stock\Collection');
            $stockCollection->getSelect()->joinInner(
                ['inventory_source_stock_link' => $stockCollection->getTable('inventory_source_stock_link')],
                'main_table.stock_id = inventory_source_stock_link.stock_id',
                []
            )->columns([
                'stock_id' => 'main_table.stock_id',
                'name' => 'main_table.name'
            ])->group(
                'main_table.stock_id'
            )->having('COUNT(inventory_source_stock_link.source_code) = 1');
            foreach ($stockCollection as $item) {
                $this->options[] = ['value' => $item->getStockId(), 'label' => $item->getName()];
            }
        }

        return $this->options;
    }

}