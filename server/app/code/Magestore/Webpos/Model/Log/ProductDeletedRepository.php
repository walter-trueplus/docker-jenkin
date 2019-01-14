<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\Log;
/**
 * Class ProductDeletedRepository
 * @package Magestore\Webpos\Model\Log
 */
class ProductDeletedRepository implements \Magestore\Webpos\Api\Log\ProductDeletedRepositoryInterface
{
    /**
     * @var \Magestore\Webpos\Api\Data\Log\ProductDeletedInterface
     */
    protected $productDeleted;
    /**
     * @var \Magestore\Webpos\Model\ResourceModel\Log\ProductDeleted
     */
    protected $productDeletedResource;

    /**
     * ProductDeletedRepository constructor.
     * @param \Magestore\Webpos\Api\Data\Log\ProductDeletedInterface $productDeleted
     * @param \Magestore\Webpos\Model\ResourceModel\Log\ProductDeleted $productDeletedResource
     */
    public function __construct(
        \Magestore\Webpos\Api\Data\Log\ProductDeletedInterface $productDeleted,
        \Magestore\Webpos\Model\ResourceModel\Log\ProductDeleted $productDeletedResource
    )
    {
        $this->productDeleted = $productDeleted;
        $this->productDeletedResource = $productDeletedResource;
    }
    /**
     * @param \Magestore\Webpos\Api\Data\Log\ProductDeletedInterface $productDeleted
     * @return \Magestore\Webpos\Api\Data\Log\ProductDeletedInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magestore\Webpos\Api\Data\Log\ProductDeletedInterface $productDeleted){
        try {
            $this->productDeletedResource->save($productDeleted);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save product deleted'));
        }
        return $productDeleted;
    }

    /**
     * @param \Magestore\Webpos\Api\Data\Log\ProductDeletedInterface $productDeleted
     * @return boolean
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magestore\Webpos\Api\Data\Log\ProductDeletedInterface $productDeleted){
        return $this->deleteById($productDeleted->getId());
    }

    /**
     * @param int $productId
     * @return boolean
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($productId){
        try {
            $productDeleted = $this->getById($productId);
            $this->productDeletedResource->delete($productDeleted);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__('Unable to delete product deleted'));
        }
    }
    /**
     * {@inheritdoc}
     */
    public function getById($productId) {
        $productDeleted = $this->productDeleted;
        $this->productDeletedResource->load($productDeleted, $productId);
        if (!$productDeleted->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Product with id "%1" does not exist.', $productId));
        } else {
            return $productId;
        }
    }
}
