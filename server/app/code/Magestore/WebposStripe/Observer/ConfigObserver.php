<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\WebposStripe\Observer;

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
     * @var \Magestore\WebposStripe\Api\StripeServiceInterface
     */
    protected $stripeService;

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
        \Magestore\WebposStripe\Api\StripeServiceInterface $stripeService,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->stripeService = $stripeService;
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
        if (strpos($path, 'save/section/webpos') !== false) {
            $group = $observer->getEvent()->getRequest()->getParam('groups');
            if ($group) {
                $isEnabled = false;
                $apiKey = '';
                if (isset($group['payment']['groups']['stripe'])){
                    $stripe = $group['payment']['groups']['stripe'];
                    if (isset($stripe['fields']['enable']['value'])){
                        $isEnabled = $stripe['fields']['enable']['value'];
                    }
                    if (isset($stripe['fields']['api_key']['value'])){
                        $apiKey = $stripe['fields']['api_key']['value'];
                    }
                    if ($this->isChangedConfig($stripe)) {
                        if ($isEnabled) {
                            if (!$this->stripeService->canConnectToApi($apiKey)) {
                                $this->messageManager->addErrorMessage(__('Error in testing API connection of Stripe, 
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
     * @param $stripe
     * @return bool
     */
    public function isChangedConfig($stripe)
    {
        $isEnabled = $stripe['fields']['enable']['value'];
        $apiKey = $stripe['fields']['api_key']['value'];

        $isEnabledFromConfig = $this->scopeConfigInterface->getValue('webpos/payment/stripe/enable');
        $apiKeyFromConfig = $this->scopeConfigInterface->getValue('webpos/payment/stripe/api_login');

        if ($isEnabled == $isEnabledFromConfig && $apiKey == $apiKeyFromConfig) {
            return false;
        } else {
            return true;
        }
    }
}