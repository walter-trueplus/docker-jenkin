<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Ui\Component\Location\Form\General;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Form\Field;

/**
 * Class StockId
 * @package Magestore\Webpos\Ui\Component\Location\Form\General
 */
class StockId extends Field
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * ResetButton constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\View\Element\UiComponentInterface[] $components
     * @param array $data
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        $components,
        array $data,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Add js listener to reset button
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.2.0
     */
    public function prepare()
    {
        parent::prepare();

        $isMSI = $this->moduleManager->isEnabled('Magento_Inventory') &&
            $this->moduleManager->isEnabled('Magento_InventoryApi');

        if (!$isMSI) {
            $config = $this->getData('config');
            $config['componentDisabled'] = true;
            $this->setData('config', $config);
        }
    }
}
