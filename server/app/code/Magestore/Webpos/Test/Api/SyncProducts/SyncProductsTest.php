<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Webpos\Test\Api\SyncProducts;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Registry;

use Magento\TestFramework\Helper\Bootstrap;

class SyncProductsTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/webpos/products/sync';
    const SERVICE_NAME = 'productsSyncRepositoryV1';

    protected $time;

    protected function setUp()
    {
        /**
         * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
         */
        //$timeZone = Bootstrap::getObjectManager()->get('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        //$currentTimeStamp = $timeZone->scopeTimeStamp();
        //$currentTime = date('Y-m-d h:i:s', $currentTimeStamp);
        //$this->time = $currentTime;
        //$datetime = Bootstrap::getObjectManager()->get('\Magento\Framework\Stdlib\DateTime\DateTime');
        //$this->time = $datetime->gmtDate();

        $datetime = date('Y-m-d h:i:s', strtotime("-1 day"));
        $this->time = $datetime;
        return $this->time;
    }

    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magestore/Webpos/Test/_files/stock.php
     */
    public function testSyncProducts()
    {
        $session_id = $this->loginAndAssignPos();
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [
                    [
                        'filters' => [
                            [
                                'field' => 'updated_at',
                                'value' => $this->time,
                                'condition_type' => 'gteq',
                            ],
                        ],
                    ],
                ],
                SearchCriteria::PAGE_SIZE => 10,
                SearchCriteria::CURRENT_PAGE => 1
            ],
        ];
        $expectedTotalCount = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData)
                    .'&show_option=1'
                    .'&pos_session=' . $session_id,
                'httpMethod' => RestRequest::HTTP_METHOD_GET,
            ]
        ];
        $response =  $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($response);

        self::assertGreaterThanOrEqual($expectedTotalCount, $response['total_count']);
        $listItems = $response['items'];
        $listSkus = [];
        foreach($listItems as $item){
            $listSkus[] = $item['sku'];
        }
        self::assertContains("SKU-1",$listSkus);
    }
    public function loginAndAssignPos(){
        /**
         * @var \Magento\Webpos\Api\Staff\LoginTest $staffLogin
         */
        $staffLogin = Bootstrap::getObjectManager()->get('\Magestore\Webpos\Test\Api\Staff\LoginTest');
        $loginPosResult = $staffLogin->getLoginResponse();
        $posAssign = Bootstrap::getObjectManager()->get('\Magestore\Webpos\Test\Api\Pos\AssignPosTest');
        $assignPosResult = $posAssign->getAssignPosResponse($loginPosResult['session_id']);
        $this->assertEquals("Enter to pos successfully", $assignPosResult['message']);
        return $loginPosResult['session_id'];
    }
}
