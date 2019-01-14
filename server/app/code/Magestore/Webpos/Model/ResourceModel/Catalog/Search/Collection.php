<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\ResourceModel\Catalog\Search;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Magestore\Webpos\Model\Catalog\Search::class,
            \Magestore\Webpos\Model\ResourceModel\Catalog\Search::class
        );
    }
    
    /**
     * Set store ID for collection
     *
     * @param int $storeId
     */
    public function setStoreId($storeId)
    {
        $this->getResource()->setStoreId($storeId);
        $this->setMainTable($this->getResource()->getMainTable());
        $this->_reset();
    }
    
    /**
     * {@inheritDoc}
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['e' => $this->getMainTable()]);
        return $this;
    }
}
