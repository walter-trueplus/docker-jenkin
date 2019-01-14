<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Api\Log;

/**
 * Interface ProductDeletedRepositoryInterface
 * @package Magestore\Webpos\Api\Log
 */
interface ProductDeletedRepositoryInterface {

    /**
     * @param \Magestore\Webpos\Api\Data\Log\ProductDeletedInterface $productDeleted
     * @return \Magestore\Webpos\Api\Data\Log\ProductDeletedInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magestore\Webpos\Api\Data\Log\ProductDeletedInterface $productDeleted);

    /**
     * @param \Magestore\Webpos\Api\Data\Log\ProductDeletedInterface $productDeleted
     * @return boolean
     */
    public function delete(\Magestore\Webpos\Api\Data\Log\ProductDeletedInterface $productDeleted);

    /**
     * @param int $productId
     * @return boolean
     */
    public function deleteById($productId);
}