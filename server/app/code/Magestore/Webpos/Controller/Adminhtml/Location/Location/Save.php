<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Controller\Adminhtml\Location\Location;

/**
 * class \Magestore\Webpos\Controller\Adminhtml\Location\Location\Save
 *
 * Save user
 * Methods:
 *  execute
 *
 * @category    Magestore
 * @package     Magestore\Webpos\Controller\Adminhtml\Location\Location
 * @module      Webpos
 * @author      Magestore Developer
 */
/**
 * Class Save
 * @package Magestore\Webpos\Controller\Adminhtml\Staff\Staff
 */
class Save extends \Magestore\Webpos\Controller\Adminhtml\Location\Location {
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('location_id');
        $data = $this->getRequest()->getPostValue();
        $arr1 = $data['general_information']['sub_general_information'];
        $arr3 = $data['general_information']['address_information'];
        $arr = array_merge($arr1, $arr3);

        if (!$arr) {
            return $this->redirectResult('*/*/');
        }

        if ($id) {
            $model = $this->locationRepository->getById($id);
        } else {
            $model = $this->locationInterfaceFactory->create();
        }

        $model->setData($arr);

        try {
            $this->locationRepository->save($model);
            $this->messageManager->addSuccessMessage(__('You saved the location.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        if ($this->getRequest()->getParam('back') == 'edit') {
            return $this->redirectResult('*/*/edit', ['id' => $model->getLocationId()]);
        }

        return $this->redirectResult('*/*/');
    }
}
