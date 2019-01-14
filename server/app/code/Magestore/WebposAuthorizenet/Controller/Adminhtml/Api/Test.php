<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\WebposAuthorizenet\Controller\Adminhtml\Api;

/**
 * Class Test
 * @package Magestore\WebposAuthorizenet\Controller\Adminhtml\Api
 */
class Test extends \Magestore\WebposAuthorizenet\Controller\Adminhtml\AbstractAction
{
    /**
     * @return \Magento\Framework\Controller\Result\Json $resultJson
     */
    public function execute()
    {
        $response = [
            'url' => '',
            'message' => '',
            'success' => true
        ];
        $isEnable = $this->getRequest()->getParam('webpos_payment_authorizenet_enable');
        if ($isEnable) {
            $apiLogin = $this->getRequest()->getParam('webpos_payment_authorizenet_api_login');
            $transactionKey = $this->getRequest()->getParam('webpos_payment_authorizenet_transaction_key');
            $connected = $this->authorizenetService->canConnectToApi($apiLogin, $transactionKey);
            $response['success'] = ($connected) ? true : false;
            $response['message'] = ($connected) ? '' : __('Connection failed. Please contact admin to check the configuration of API.');
        } else {
            $message = $this->authorizenetService->getConfigurationError();
            $response['success'] = false;
            $response['message'] = __($message);
        }
        return $this->createJsonResult($response);
    }
}
