<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Setup;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magestore\Webpos\Helper\Product\CustomSale $customSaleHelper
     */
    protected $customSaleHelper;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    protected $deployService;

    /**
     * UpgradeSchema constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magestore\Webpos\Helper\Product\CustomSale $customSaleHelper
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magestore\Webpos\Helper\Product\CustomSale $customSaleHelper,
        ModuleDataSetupInterface $moduleDataSetup,
        \Magestore\Webpos\Model\Deploy\DeployService $deployService
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->_eavAttribute = $eavAttribute;
        $this->productMetadata = $productMetadata;
        $this->moduleManager = $moduleManager;
        $this->customSaleHelper = $customSaleHelper;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->deployService = $deployService;
    }

    /**
     * {@inheritdoc}
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        /* deploy pos app into pub */
        $this->deployService->execute();

        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.1.0.1', '<')) {
            // change column
            $setup->getConnection()->modifyColumn(
                $setup->getTable('webpos_staff'),
                'role_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => true
                ]
            );

            if ($setup->tableExists('webpos_authorization_role')) {
                // drop foreign key of webpos_staff table
                $setup->getConnection()->dropForeignKey(
                    $setup->getTable('webpos_staff'),
                    $setup->getFkName(
                        'webpos_staff',
                        'role_id',
                        'webpos_authorization_role',
                        'role_id'
                    )
                );
                // add foreign key for webpos_staff table
                $setup->getConnection()->addForeignKey(
                    $setup->getFkName('webpos_staff', 'role_id', 'webpos_authorization_role', 'role_id'),
                    $setup->getTable('webpos_staff'),
                    'role_id',
                    $setup->getTable('webpos_authorization_role'),
                    'role_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
                );
            }
        }

        if (
            version_compare($context->getVersion(), '0.1.0.2', '<')
            && $setup->tableExists('webpos_authorization_role')
        ) {
            // remove column denomination_ids on POS table
            $setup->getConnection()->dropColumn(
                $setup->getTable('webpos_pos'),
                'denomination_ids'
            );

            // alter table location
            $this->alterTableLocation($setup);

            // remove column maximum_discount_percent on role table
            $setup->getConnection()->dropColumn(
                $setup->getTable('webpos_authorization_role'),
                'maximum_discount_percent'
            );

            // alter table staff
            $this->alterTableStaff($setup);
        }

        if (version_compare($context->getVersion(), '0.1.0.3', '<')) {
            // alter table order
            $this->alterTableOrder($setup);
        }

        if (version_compare($context->getVersion(), '0.1.0.4', '<')) {
            // create payment table
            $this->createPaymentTable($setup);
        }

        if (version_compare($context->getVersion(), '0.1.0.5', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('webpos_location'),
                'country',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '255',
                    'comment' => 'country'
                )
            );
        }

        if (version_compare($context->getVersion(), '0.1.0.6', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('webpos_session'),
                'has_exception',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'length' => null,
                    'default' => 0,
                    'comment' => 'has_exception'
                )
            );

            // drop foreign key of webpos_session table
            $setup->getConnection()->dropForeignKey(
                $setup->getTable('webpos_session'),
                $setup->getFkName(
                    'webpos_session',
                    'staff_id',
                    'webpos_staff',
                    'staff_id'
                )
            );
            // change column staff_id on table webpos_session
            $setup->getConnection()->changeColumn(
                $setup->getTable('webpos_session'),
                'staff_id',
                'staff_id',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'length' => '',
                    'comment' => 'staff_id',
                    'unsigned' => true
                )
            );
            // add foreign key for webpos_session table
            $setup->getConnection()->addForeignKey(
                $setup->getFkName('webpos_session', '	staff_id', 'webpos_staff', 'staff_id'),
                $setup->getTable('webpos_session'),
                'staff_id',
                $setup->getTable('webpos_staff'),
                'staff_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            );
        }

        if (version_compare($context->getVersion(), '0.1.0.8', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('quote_item'),
                'tmp_item_id',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'length' => '255',
                    'comment' => 'tmp_item_id'
                )
            );
        }


        if (version_compare($context->getVersion(), '0.1.0.9', '<')) {
            $eavSetup = $this->eavSetupFactory->create();
            // add customer_attribute to customer
            $eavSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY, 'customer_telephone', [
                'type' => 'varchar',
                'label' => 'Customer Telephone',
                'input' => 'text',
                'required' => false,
                'visible' => false,
                'system' => 0,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'sort_order' => '200'
            ]);
        }

        if (version_compare($context->getVersion(), '0.1.0.10', '<') && $this->moduleManager->isEnabled('Magestore_InventorySuccess')) {
            $setup->getConnection()->dropForeignKey(
                $setup->getTable('webpos_pos'),
                $setup->getFkName('webpos_pos', 'location_id', 'webpos_location', 'location_id')
            );

            $setup->getConnection()->addForeignKey(
                $setup->getFkName('webpos_pos', 'location_id', 'os_warehouse', 'warehouse_id'),
                $setup->getTable('webpos_pos'),
                'location_id',
                $setup->getTable('os_warehouse'),
                'warehouse_id',
                Table::ACTION_CASCADE
            );
        }

        if (version_compare($context->getVersion(), '0.1.0.11', '<')) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('webpos_customer_deleted')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'id'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true, 'default' => 0],
                'customer_id'
            )->addColumn(
                'deleted_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Deleted At'
            )->addIndex(
                $setup->getIdxName('webpos_customer_deleted', ['customer_id']),
                ['customer_id']
            )->addIndex(
                $setup->getIdxName('webpos_customer_deleted', ['deleted_at']),
                ['deleted_at']
            );

            $setup->getConnection()->createTable($table);

            $table = $setup->getConnection()->newTable(
                $setup->getTable('webpos_product_deleted')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'id'
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true, 'default' => 0],
                'product_id'
            )->addColumn(
                'deleted_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Deleted At'
            )->addIndex(
                $setup->getIdxName('webpos_product_deleted', ['product_id']),
                ['product_id']
            )->addIndex(
                $setup->getIdxName('webpos_product_deleted', ['deleted_at']),
                ['deleted_at']
            );

            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '0.1.0.12', '<')) {
            // change column staff_id on table webpos_pos
            $setup->getConnection()->changeColumn(
                $setup->getTable('webpos_pos'),
                'staff_id',
                'staff_id',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'length' => '',
                    'comment' => 'staff_id',
                    'unsigned' => true
                )
            );
            // add foreign key for webpos_pos table
            $setup->getConnection()->addForeignKey(
                $setup->getFkName('webpos_pos', '	staff_id', 'webpos_staff', 'staff_id'),
                $setup->getTable('webpos_pos'),
                'staff_id',
                $setup->getTable('webpos_staff'),
                'staff_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            );
        }

        if (version_compare($context->getVersion(), '0.1.0.13', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_item'),
                'tmp_item_id',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'length' => '255',
                    'comment' => 'tmp_item_id'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_item'),
                'pos_base_original_price_excl_tax',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'default' => null,
                    'length' => '12,4',
                    'comment' => 'POS BASE ORIGINAL PRICE EXCLUDE TAX'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_item'),
                'pos_original_price_excl_tax',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'default' => null,
                    'length' => '12,4',
                    'comment' => 'POS ORIGINAL PRICE EXCLUDE TAX'
                )
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_item'),
                'pos_base_original_price_incl_tax',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'default' => null,
                    'length' => '12,4',
                    'comment' => 'POS BASE ORIGINAL PRICE INCLUDE TAX'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_item'),
                'pos_original_price_incl_tax',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'default' => null,
                    'length' => '12,4',
                    'comment' => 'POS ORIGINAL PRICE INCLUDE TAX'
                )
            );
        }

        if (version_compare($context->getVersion(), '0.1.0.14', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'pos_staff_id',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'length' => '10',
                    'comment' => 'Pos Staff ID'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'pos_staff_name',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '255',
                    'comment' => 'Pos Staff Name'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'pos_delivery_date',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    'nullable' => true,
                    'length' => null,
                    'comment' => 'Pos Delivery Date'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('webpos_order_payment'),
                'payment_date',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    'nullable' => true,
                    'length' => null,
                    'comment' => 'Payment Date'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'pos_location_id',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'length' => '10',
                    'comment' => 'Pos Location ID'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'pos_fulfill_online',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Pos Fulfill Online'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_grid'),
                'pos_fulfill_online',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Pos Fulfill Online'
                )
            );
        }

        if (version_compare($context->getVersion(), '0.1.0.15', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_creditmemo'),
                'pos_location_id',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'length' => '10',
                    'comment' => 'Pos Location ID'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_creditmemo_grid'),
                'pos_location_id',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'length' => '10',
                    'comment' => 'Pos Location ID'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_creditmemo_item'),
                'pos_location_id',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'length' => '10',
                    'comment' => 'Pos Location ID'
                )
            );
        }

        if (version_compare($context->getVersion(), '0.1.0.16', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'pos_change',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'default' => null,
                    'length' => '12,4',
                    'comment' => 'POS CHANGE'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'base_pos_change',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'default' => null,
                    'length' => '12,4',
                    'comment' => 'BASE POS CHANGE'
                )
            );
        }

        if (version_compare($context->getVersion(), '0.1.0.17', '<')) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('webpos_order_deleted')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'id'
            )->addColumn(
                'order_increment_id',
                Table::TYPE_TEXT,
                32,
                ['nullable' => false, 'unsigned' => true, 'default' => null],
                'order_id'
            )->addColumn(
                'deleted_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Deleted At'
            )->addIndex(
                $setup->getIdxName('webpos_order_deleted', ['order_increment_id']),
                ['order_increment_id']
            )->addIndex(
                $setup->getIdxName('webpos_order_deleted', ['deleted_at']),
                ['deleted_at']
            );

            $setup->getConnection()->createTable($table);

            $setup->getConnection()->addColumn(
                $setup->getTable('webpos_order_payment'),
                'card_type',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'length' => '128',
                    'comment' => 'Card Type'
                )
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('webpos_order_payment'),
                'is_pay_later',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'default' => 0,
                    'length' => '1',
                    'comment' => 'Is Pay Later'
                )
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('webpos_order_payment'),
                'type',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'default' => 0,
                    'length' => '1',
                    'comment' => 'Type'
                )
            );
        }

        if (version_compare($context->getVersion(), '0.1.0.18', '<')) {
            $connection = $setup->getConnection();

            if ($setup->tableExists('webpos_cash_denomination')) {
                $connection->dropTable($setup->getTable('webpos_cash_denomination'));
            }

            $table = $setup->getConnection()->newTable(
                $setup->getTable('webpos_cash_denomination')
            )->addColumn(
                'denomination_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'denomination_id'
            )->addColumn(
                'denomination_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'denomination_name'
            )->addColumn(
                'denomination_value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => true],
                'denomination_value'
            )->addColumn(
                'location_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => ''],
                'location_ids'
            )->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'sort_order'
            );

            $connection->createTable($table);

            if ($setup->tableExists('webpos_shift')) {
                $connection->dropTable($setup->getTable('webpos_shift'));
            }

            $tableShift = $connection->newTable(
                $setup->getTable('webpos_shift')
            )->addColumn(
                'shift_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Shift ID'
            )->addColumn(
                'shift_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Shift Increment ID'
            )->addColumn(
                'staff_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Staff ID'
            )->addColumn(
                'pos_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'POS ID'
            )->addColumn(
                'location_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Location ID'
            )->addColumn(
                'opening_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false],
                'Opening Amount'
            )->addColumn(
                'base_opening_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false],
                'Base Opening Amount'
            )->addColumn(
                'closed_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false],
                'Closed Amount'
            )->addColumn(
                'base_closed_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false],
                'Base Closed Amount'
            )->addColumn(
                'opened_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Opened At'
            )->addColumn(
                'closed_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Closed At'
            )->addColumn(
                'closed_note',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Closed Note'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['unsigned' => true, 'nullable' => false],
                'Status'
            )->addColumn(
                'base_currency_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                [],
                'Base Currency Code'
            )->addColumn(
                'shift_currency_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                [],
                'Shift Currency Code'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
                'Updated At'
            );

            $connection->createTable($tableShift);

            if ($setup->tableExists('webpos_cash_transaction')) {
                $connection->dropTable($setup->getTable('webpos_cash_transaction'));
            }

            $tableCashTransaction = $connection->newTable(
                $setup->getTable('webpos_cash_transaction')
            )->addColumn(
                'transaction_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Transaction ID'
            )->addColumn(
                'transaction_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Transaction Increment ID'
            )->addColumn(
                'shift_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Shift Increment ID'
            )->addColumn(
                'location_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Location ID'
            )->addColumn(
                'order_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Order Increment ID'
            )->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false],
                'Value'
            )->addColumn(
                'base_value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false],
                'Base Value'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'note',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Note'
            )->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Type'
            )->addColumn(
                'base_currency_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                [],
                'Base Currency Code'
            )->addColumn(
                'transaction_currency_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                [],
                'Transaction Currency Code'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
                'Updated At'
            );

            $connection->createTable($tableCashTransaction);


            if (!$connection->tableColumnExists(
                $setup->getTable('webpos_order_payment'), 'shift_increment_id')
            ) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('webpos_order_payment'),
                    'shift_increment_id',
                    array(
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Shift increment ID'
                    )
                );
            }

        }

        if (version_compare($context->getVersion(), '0.1.0.19', '<')) {
            $connection = $setup->getConnection();
            if (!$connection->tableColumnExists(
                $setup->getTable('webpos_order_payment'), 'receipt')
            ) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('webpos_order_payment'),
                    'receipt',
                    array(
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Receipt'
                    )
                );
            }

        }

        if (version_compare($context->getVersion(), '0.1.0.20', '<')) {
            $this->createCustomSale($setup);
        }

        if (version_compare($context->getVersion(), '0.1.0.21', '<')) {
            if (!$setup->getConnection()->tableColumnExists(
                $setup->getTable('catalogrule_product_price'), 'updated_time')
            ) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('catalogrule_product_price'),
                    'updated_time',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        'nullable' => false,
                        'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                        'comment' => 'Updated Time'
                    ]
                );
            }
            if ($setup->getConnection()->isTableExists('catalogrule_product_price_replica')) {
                if (!$setup->getConnection()->tableColumnExists(
                    $setup->getTable('catalogrule_product_price_replica'), 'updated_time')
                ) {
                    $setup->getConnection()->addColumn(
                        $setup->getTable('catalogrule_product_price_replica'),
                        'updated_time',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            'nullable' => false,
                            'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                            'comment' => 'Updated Time'
                        ]
                    );
                }
            }
        }

        if (version_compare($context->getVersion(), '0.1.0.22', '<')) {
            if (!$setup->getConnection()->tableColumnExists(
                $setup->getTable('sales_creditmemo_item'), 'pos_base_original_price_excl_tax')
            ) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('sales_creditmemo_item'),
                    'pos_base_original_price_excl_tax',
                    array(
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'nullable' => true,
                        'default' => null,
                        'length' => '12,4',
                        'comment' => 'POS BASE ORIGINAL PRICE EXCLUDE TAX'
                    )
                );
            }
            if (!$setup->getConnection()->tableColumnExists(
                $setup->getTable('sales_creditmemo_item'), 'pos_original_price_excl_tax')
            ) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('sales_creditmemo_item'),
                    'pos_original_price_excl_tax',
                    array(
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'nullable' => true,
                        'default' => null,
                        'length' => '12,4',
                        'comment' => 'POS ORIGINAL PRICE EXCLUDE TAX'
                    )
                );
            }
            if (!$setup->getConnection()->tableColumnExists(
                $setup->getTable('sales_creditmemo_item'), 'pos_base_original_price_incl_tax')
            ) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('sales_creditmemo_item'),
                    'pos_base_original_price_incl_tax',
                    array(
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'nullable' => true,
                        'default' => null,
                        'length' => '12,4',
                        'comment' => 'POS BASE ORIGINAL PRICE INCLUDE TAX'
                    )
                );
            }
            if (!$setup->getConnection()->tableColumnExists(
                $setup->getTable('sales_creditmemo_item'), 'pos_original_price_incl_tax')
            ) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('sales_creditmemo_item'),
                    'pos_original_price_incl_tax',
                    array(
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'nullable' => true,
                        'default' => null,
                        'length' => '12,4',
                        'comment' => 'POS ORIGINAL PRICE INCLUDE TAX'
                    )
                );
            }
        }

        if (version_compare($context->getVersion(), '1.0.0.3', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_item'),
                'os_pos_custom_price_reason',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '127',
                    'comment' => 'POS Custom Price Reason'
                )
            );
        }
        if (version_compare($context->getVersion(), '1.0.1.1', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'pos_pre_total_paid',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'default' => null,
                    'length' => '12,4',
                    'comment' => 'POS PREVIOUS TOTAL PAID'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'pos_base_pre_total_paid',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'default' => null,
                    'length' => '12,4',
                    'comment' => 'POS BASE PREVIOUS TOTAL PAID'
                )
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'os_pos_custom_discount_reason',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '255',
                    'comment' => 'POS Custom Discount Reason'
                )
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'os_pos_custom_discount_type',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '2',
                    'comment' => 'POS Custom Discount Type'
                )
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'os_pos_custom_discount_amount',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'default' => null,
                    'length' => '12,4',
                    'comment' => 'POS Custom Discount Amount'
                )
            );
        }
        if (version_compare($context->getVersion(), '1.0.1.2', '<')) {
            if (!$setup->getConnection()->tableColumnExists(
                $setup->getTable('cataloginventory_stock_item'), 'updated_time')
            ) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('cataloginventory_stock_item'),
                    'updated_time',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        'nullable' => false,
                        'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                        'comment' => 'Updated Time'
                    ]
                );
            }
        }
        if (version_compare($context->getVersion(), '1.1.0', '<=')) {
            $setup->getConnection()->dropForeignKey(
                $setup->getTable('webpos_pos'),
                $setup->getFkName('webpos_pos', 'location_id', 'webpos_location', 'location_id')
            );
            $setup->getConnection()->dropForeignKey(
                $setup->getTable('webpos_pos'),
                $setup->getFkName('os_warehouse', 'warehouse_id', 'webpos_location', 'location_id')
            );
        }


        if (version_compare($context->getVersion(), '1.1.0.1', '<=')) {
            if (!$setup->getConnection()->tableColumnExists(
                $setup->getTable('webpos_order_payment'), 'increment_id')
            ) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('webpos_order_payment'),
                    'increment_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'size' => 32,
                        'comment' => 'Payment Increment Id'
                    ]
                );
            }

            if (!$setup->getConnection()->tableColumnExists(
                $setup->getTable('webpos_order_payment'), 'parent_increment_id')
            ) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('webpos_order_payment'),
                    'parent_increment_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'size' => 32,
                        'comment' => 'Parent Payment Increment Id'
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '1.1.1', '<=')) {
            if (!$setup->getConnection()->tableColumnExists(
                $setup->getTable('webpos_location'), 'stock_id')
            ) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('webpos_location'),
                    'stock_id',
                    array(
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'comment' => 'Stock Id'
                    )
                );
            }
        }

        $setup->endSetup();
    }

    protected function alterTableOrder(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'pos_id',
            array(
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'POS ID'
            )
        );
    }

    protected function alterTableLocation(SchemaSetupInterface $setup)
    {
        // drop column
        $setup->getConnection()->dropColumn(
            $setup->getTable('webpos_location'),
            'address'
        );
        $setup->getConnection()->dropColumn(
            $setup->getTable('webpos_location'),
            'store_id'
        );

        // add new column
        $setup->getConnection()->addColumn(
            $setup->getTable('webpos_location'),
            'telephone',
            array(
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'length' => '127',
                'comment' => 'telephone'
            )
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('webpos_location'),
            'email',
            array(
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'length' => '127',
                'comment' => 'email'
            )
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('webpos_location'),
            'street',
            array(
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'length' => '255',
                'comment' => 'street'
            )
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('webpos_location'),
            'city',
            array(
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'length' => '255',
                'comment' => 'city'
            )
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('webpos_location'),
            'region',
            array(
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'length' => '255',
                'comment' => 'region'
            )
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('webpos_location'),
            'region_id',
            array(
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'region_id'
            )
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('webpos_location'),
            'country_id',
            array(
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'length' => '128',
                'comment' => 'country_id'
            )
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('webpos_location'),
            'postcode',
            array(
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'length' => '128',
                'comment' => 'postcode'
            )
        );
    }

    protected function alterTableStaff(SchemaSetupInterface $setup)
    {
        //drop column
        $setup->getConnection()->dropColumn(
            $setup->getTable('webpos_staff'),
            'customer_groups'
        );
        $setup->getConnection()->dropColumn(
            $setup->getTable('webpos_staff'),
            'pos_ids'
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('webpos_staff'),
            'pin',
            array(
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => true,
                'length' => '4',
                'comment' => 'pin'
            )
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    protected function createPaymentTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->dropTable($setup->getTable('webpos_order_payment'));

        $tablePayment = $setup->getConnection()->newTable(
            $setup->getTable('webpos_order_payment')
        )->addColumn(
            'payment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'payment_id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true, 'default' => 0],
            'order_id'
        )->addColumn(
            'shift_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'shift_id'
        )->addColumn(
            'base_amount_paid',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true],
            'base_amount_paid'
        )->addColumn(
            'amount_paid',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true],
            'amount_paid'
        )->addColumn(
            'method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            ['nullable' => true],
            'method'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            ['nullable' => true],
            'title'
        )->addColumn(
            'transaction_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'transaction_id'
        )->addColumn(
            'invoice_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'invoice_id'
        )->addColumn(
            'reference_number',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'reference_number'
        )->addColumn(
            'card_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            ['nullable' => true],
            'card_type'
        )->addForeignKey(
            $setup->getFkName('webpos_order_payment', 'order_id', 'sales_order', 'entity_id'),
            'order_id',
            $setup->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $setup->getConnection()->createTable($tablePayment);
    }

    /**
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createCustomSale()
    {
        $this->customSaleHelper->createProduct($this->moduleDataSetup);
    }
}