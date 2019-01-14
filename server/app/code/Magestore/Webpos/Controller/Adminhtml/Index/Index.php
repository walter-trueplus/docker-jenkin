<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Controller\Adminhtml\Index;

/**
 * Class Index
 * @package Magestore\Webpos\Controller\Adminhtml\Index
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        if(substr($baseUrl, -1) != '/') {
            $baseUrl .= '/';
        }
        if(substr($baseUrl, -10) == 'index.php/') {
            $baseUrl = str_replace('index.php/', '', $baseUrl);
        }
        $url = $baseUrl.'pub/apps/pos';
        if(strpos(strtolower($this->getRequest()->getServer('SERVER_SOFTWARE')), 'nginx') !== FALSE){
            $url = $url. '/index.html';
        }
        $this->_redirect($url);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Webpos::checkout');
    }
}
