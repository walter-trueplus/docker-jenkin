<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\WebposStripe\Controller\Adminhtml\Api;

/**
 * Class Test
 * @package Magestore\WebposStripe\Controller\Adminhtml\Api
 */
class Test extends \Magestore\WebposStripe\Controller\Adminhtml\AbstractAction
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
        $isEnable = $this->getRequest()->getParam('webpos_payment_stripe_enable');
        if ($isEnable) {
            $apiLogin = $this->getRequest()->getParam('webpos_payment_stripe_api_key');
            $connected = $this->stripeService->canConnectToApi($apiLogin);
            $response['success'] = ($connected) ? true : false;
            $response['message'] = ($connected) ? '' : __('Connection failed. Please contact admin to check the configuration of API.');
        } else {
            $message = $this->stripeService->getConfigurationError();
            $response['success'] = false;
            $response['message'] = __($message);
        }
        return $this->createJsonResult($response);
    }
}
