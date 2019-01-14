<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Model\Indexer;

class Product implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var \Magestore\Webpos\Model\Indexer\Product\Action\Row
     */
    protected $productFlatIndexerRow;
    
    /**
     * @var \Magestore\Webpos\Model\Indexer\Product\Action\Rows
     */
    protected $productFlatIndexerRows;
    
    /**
     * @var \Magestore\Webpos\Model\Indexer\Product\Action\Full
     */
    protected $productFlatIndexerFull;
    
    /**
     * @param \Magestore\Webpos\Model\Indexer\Product\Action\Row $productFlatIndexerRow
     * @param \Magestore\Webpos\Model\Indexer\Product\Action\Rows $productFlatIndexerRows
     * @param \Magestore\Webpos\Model\Indexer\Product\Action\Full $productFlatIndexerFull
     */
    public function __construct(
        \Magestore\Webpos\Model\Indexer\Product\Action\Row $productFlatIndexerRow,
        \Magestore\Webpos\Model\Indexer\Product\Action\Rows $productFlatIndexerRows,
        \Magestore\Webpos\Model\Indexer\Product\Action\Full $productFlatIndexerFull
    ) {
        $this->productFlatIndexerRow = $productFlatIndexerRow;
        $this->productFlatIndexerRows = $productFlatIndexerRows;
        $this->productFlatIndexerFull = $productFlatIndexerFull;
    }
    
    /**
     * {@inheritDoc}
     */
    public function executeFull()
    {
        $this->productFlatIndexerFull->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function executeRow($id)
    {
        $this->productFlatIndexerRow->execute($id);
    }

    /**
     * {@inheritDoc}
     */
    public function executeList(array $ids)
    {
        $this->productFlatIndexerRows->execute($ids);
    }

    /**
     * {@inheritDoc}
     */
    public function execute($ids)
    {
        $this->executeList($ids);
    }
}
