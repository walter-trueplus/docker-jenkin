<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Controller\Adminhtml\Pos;

/**
 * class \Magestore\Webpos\Controller\Adminhtml\Pos\Delete
 *
 * Delete Pos
 * Methods:
 *  execute
 *
 * @category    Magestore
 * @package     Magestore\Webpos\Controller\Adminhtml\Pos
 * @module      Webpos
 * @author      Magestore Developer
 */
class ForceSignOut extends \Magestore\Webpos\Controller\Adminhtml\Pos\AbstractAction
{
    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $modelId = $this->getRequest()->getParam('id');
        if ($modelId > 0) {
            $model = $this->posInterfaceFactory->create()->load($modelId);

            /* notify to admin when force signout */
            if($model->getStaffId()){
                $currentShift = $this->shiftRepository->getCurrentShiftByPosId($model->getPosId());
                $staff = $this->staffRepository->getById($model->getStaffId());
                if($staff->getName() && $currentShift->getId()) {
                    $this->messageManager->addNotice(
                        __('POS is still working in the session. Please close current session!')
                    );
                }
            }

            // dispatch event to logout POS
            $this->dispatchService->dispatchEventForceSignOut($model->getStaffId(), $model->getPosId());

            $model->setStaffId(null);
            $this->posRepository->save($model);
            $this->sessionRepository->signOutPos($modelId);
        }
        return $resultRedirect->setPath('*/*/edit', ['id' =>$modelId]);
    }

}