<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Helper;

/**
 * Class Data
 * @package Magestore\Webpos\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper

{
    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param $modelName
     * @param array $arguments
     * @return mixed
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function getModel($modelName, array $arguments = [])
    {
        $model = $this->objectManager->create('\Magestore\Webpos\\' . $modelName);
        if (!$model) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('%1 doesn\'t extends \Magento\Framework\Model\AbstractModel', $modelName)
            );
        }
        return $model;
    }

    /**
     *
     * @param string $path
     * @return string
     */
    public function getStoreConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    /**
     * @return mixed
     */
    public function getCurrentLocationId()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $sessionRepository = $objectManager->get('\Magestore\Webpos\Api\Staff\SessionRepositoryInterface');
        $request = $objectManager->get('Magento\Framework\App\RequestInterface');
        $session = $sessionRepository->getBySessionId($request->getParam(\Magestore\WebposIntegration\Controller\Rest\RequestProcessor::SESSION_PARAM_KEY));
        $locationId = $session->getLocationId();
        return $locationId;
    }

    /**
     *
     * @return string
     */
    public function getWebPosImages()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . 'webpos/logo/' . $this->getWebPosLogoSetting();
    }

    /**
     * @return mixed
     */
    public function getWebPosLogoSetting()
    {
        return $this->scopeConfig->getValue('webpos/general/webpos_logo', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isRewardPointEnable()
    {
        if ($this->_moduleManager->isEnabled('Magestore_Rewardpoints')) {
            if ($this->getStoreConfig('rewardpoints/general/enable')) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isStoreCreditEnable()
    {
        if ($this->_moduleManager->isEnabled('Magestore_Customercredit')) {
            if ($this->getStoreConfig('customercredit/general/enable')) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isEnabledInventory()
    {
        return $this->_moduleManager->isEnabled('Magestore_InventorySuccess');
    }

    /**
     * @return bool
     */
    public function isEnabledGiftCard()
    {
        if ($this->_moduleManager->isEnabled('Magestore_Giftvoucher')) {
            if ($this->getStoreConfig('giftvoucher/general/active')) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isEnableSession()
    {
        if ($this->getStoreConfig('webpos/session/enable')) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isCashControl()
    {
        if ($this->isEnableSession() &&
            $this->getStoreConfig('webpos/session/enable_cash_control')
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get current store view
     * @todo: will change in the future to current store view, now it's default store view
     *
     * @return \Magento\Store\Api\Data\StoreInterface|null
     */
    public function getCurrentStoreView()
    {
        try {
            /*return $this->_storeManager->getStore();*/
            return $this->_storeManager->getDefaultStoreView();
        } catch (\Exception $e) {
            return $this->_storeManager->getStore();
        }
    }
    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }
}