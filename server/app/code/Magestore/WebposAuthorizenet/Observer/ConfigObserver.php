<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\WebposAuthorizenet\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ConfigObserver
 * @package Magestore\WebposAuthorizenet\Observer
 */
class ConfigObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigInterface;
    /**
     * @var \Magestore\WebposAuthorizenet\Api\AuthorizenetServiceInterface
     */
    protected $authorizenetService;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * ConfigObserver constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     * @param \Magestore\WebposAuthorizenet\Api\AuthorizenetServiceInterface $authorizenetService
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magestore\WebposAuthorizenet\Api\AuthorizenetServiceInterface $authorizenetService,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->authorizenetService = $authorizenetService;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @param EventObserver $observer
     * @return $this|\Magento\Framework\Controller\Result\Redirect|$this
     */
    public function execute(EventObserver $observer)
    {
        $path = $observer->getEvent()->getRequest()->getPathInfo();
        if (strpos($path, 'save/section/webpos') !== false){
            $group = $observer->getEvent()->getRequest()->getParam('groups');
            if ($group){
                $isEnabled = false;
                $apiLogin = '';
                $transactionKey = '';
                if (isset($group['payment']['groups']['authorizenet'])){
                    $authorize = $group['payment']['groups']['authorizenet'];
                    if (isset($authorize['fields']['enable']['value'])){
                        $isEnabled = $authorize['fields']['enable']['value'];
                    }
                    if (isset($authorize['fields']['api_login']['value'])){
                        $apiLogin = $authorize['fields']['api_login']['value'];
                    }
                    if (isset($authorize['fields']['transaction_key']['value'])){
                        $transactionKey = $authorize['fields']['transaction_key']['value'];
                    }
                    if ($this->isChangedConfig($authorize)) {
                        if ($isEnabled){
                            if (!$this->authorizenetService->canConnectToApi($apiLogin, $transactionKey)){
                                $this->messageManager->addErrorMessage(__('Error in testing API connection of Authorize.net,
                                    please check configuration again.'));
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param $authorize
     * @return bool
     */
    public function isChangedConfig($authorize)
    {
        $isEnabled = $authorize['fields']['enable']['value'];
        $apiLogin = $authorize['fields']['api_login']['value'];
        $transactionKey = $authorize['fields']['transaction_key']['value'];

        $isEnabledFromConfig = $this->scopeConfigInterface->getValue('webpos/payment/authorizenet/enable');
        $apiLoginFromConfig = $this->scopeConfigInterface->getValue('webpos/payment/authorizenet/api_login');
        $transactionKeyFromConfig = $this->scopeConfigInterface->getValue('webpos/payment/authorizenet/transaction_key');

        if ($isEnabled == $isEnabledFromConfig && $apiLogin == $apiLoginFromConfig
            && $transactionKey == $transactionKeyFromConfig) {
            return false;
        } else {
            return true;
        }
    }
}
