<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\WebposIntegration\Controller\Rest;

use Magento\Framework\Exception\AuthorizationException;
use Magestore\Webpos\Helper\Profiler;
use Magento\Framework\Webapi\Rest\Response\FieldsFilter;

/**
 * Class RequestProcessor
 * @package Magestore\WebposIntegration\Controller\Rest
 */
class RequestProcessor
{
    const SESSION_PARAM_KEY = 'pos_session';
    const MANAGE_POS_RESOURCE = 'Magestore_Webpos::manage_pos';

    /**
     * @var \Magento\Webapi\Controller\Rest\InputParamsResolver
     */
    protected $inputParamsResolver;

    /**
     * @var \Magento\Framework\Webapi\Rest\Response
     */
    protected $response;

    /**
     * @var \Magento\Framework\Webapi\ServiceOutputProcessor
     */
    protected $serviceOutputProcessor;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @var \Magento\Framework\Webapi\Rest\Response\FieldsFilter
     */
    protected $fieldsFilter;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magestore\Webpos\Api\Staff\StaffManagementInterface
     */
    protected $staffManagement;
    /**
     * @var \Magestore\Webpos\Api\Staff\SessionRepositoryInterface
     */
    protected $sessionRepository;
    /**
     * @var \Magestore\Webpos\Api\Location\LocationRepositoryInterface
     */
    protected $locationRepository;

    /**
     * RequestProcessor constructor.
     * @param \Magento\Framework\Webapi\Rest\Response $response
     * @param \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver
     * @param \Magento\Framework\Webapi\ServiceOutputProcessor $serviceOutputProcessor
     * @param \Magento\Framework\Webapi\Rest\Response\FieldsFilter $fieldsFilter
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\Webpos\Api\Staff\StaffManagementInterface $staffManagement
     * @param \Magestore\Webpos\Api\Staff\SessionRepositoryInterface $sessionRepository
     * @param \Magestore\Webpos\Api\Location\LocationRepositoryInterface $locationRepository
     */
    public function __construct(
        \Magento\Framework\Webapi\Rest\Response $response,
        \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver,
        \Magento\Framework\Webapi\ServiceOutputProcessor $serviceOutputProcessor,
        \Magento\Framework\Webapi\Rest\Response\FieldsFilter $fieldsFilter,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Webpos\Api\Staff\StaffManagementInterface $staffManagement,
        \Magestore\Webpos\Api\Staff\SessionRepositoryInterface $sessionRepository,
        \Magestore\Webpos\Api\Location\LocationRepositoryInterface $locationRepository
    ){
        $this->response = $response;
        $this->inputParamsResolver = $inputParamsResolver;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->deploymentConfig = $deploymentConfig;
        $this->fieldsFilter = $fieldsFilter;
        $this->objectManager = $objectManager;
        $this->staffManagement = $staffManagement;
        $this->sessionRepository = $sessionRepository;
        $this->locationRepository = $locationRepository;
    }

    /**
     *  {@inheritdoc}
     */
    public function process(\Magento\Framework\Webapi\Rest\Request $request)
    {
        $sessionId = $request->getParam(self::SESSION_PARAM_KEY);
        $route = $this->inputParamsResolver->getRoute();
        $aclResource = $route->getAclResources();
        if ($route->getServiceMethod() != 'logout') {
            try{
                $session = $this->sessionRepository->getBySessionId($sessionId);
            }catch (\Exception $e){
                throw new AuthorizationException(
                    __($e->getMessage())
                );
            }
            if ($route->getServiceClass() != 'Magestore\Webpos\Api\Staff\StaffManagementInterface' && $route->getServiceMethod() != 'logout') {
                if ($session->getHasException() == \Magestore\Appadmin\Api\Event\DispatchServiceInterface::EXCEPTION_CODE_FORCE_SIGN_OUT) {
                    throw new AuthorizationException(
                        __(\Magestore\Appadmin\Api\Event\DispatchServiceInterface::EXCEPTION_MESSAGE),
                        new \Exception(),
                        \Magestore\Appadmin\Api\Event\DispatchServiceInterface::EXCEPTION_CODE_FORCE_SIGN_OUT
                    );
                } elseif ($session->getHasException() == \Magestore\Appadmin\Api\Event\DispatchServiceInterface::EXCEPTION_CODE_FORCE_CHANGE_POS) {
                    $locations = $this->locationRepository->getListAvailable($session->getStaffId());
                    if (!$locations) {
                        throw new AuthorizationException(
                            __(\Magestore\Appadmin\Api\Event\DispatchServiceInterface::EXCEPTION_MESSAGE),
                            new \Exception(),
                            \Magestore\Appadmin\Api\Event\DispatchServiceInterface::EXCEPTION_CODE_FORCE_SIGN_OUT
                        );
                    }
                    if (($route->getServiceClass() != 'Magestore\Webpos\Api\Pos\PosRepositoryInterface' && $route->getServiceMethod() != 'assignPos')
                        && ($route->getServiceClass() != 'Magestore\Webpos\Api\Location\LocationRepositoryInterface' && $route->getServiceMethod() != 'getList')) {
                        throw new AuthorizationException(
                            __(\Magestore\Appadmin\Api\Event\DispatchServiceInterface::EXCEPTION_MESSAGE),
                            new \Exception(),
                            \Magestore\Appadmin\Api\Event\DispatchServiceInterface::EXCEPTION_CODE_FORCE_CHANGE_POS
                        );
                    }
                }
            }
            try{
                $currentStaff = $this->staffManagement->authorizeSession($sessionId);
            }catch (\Exception $e){
                throw new AuthorizationException(
                    __($e->getMessage())
                );
            }
            $this->logoutCustomer();
            if ($currentStaff) {
                if (!$this->isAllowWebPosPermission($aclResource, $currentStaff)) {
                    $params = ['resources' => implode(', ', $route->getAclResources())];
                    throw new AuthorizationException(
                        __(AuthorizationException::NOT_AUTHORIZED, $params)
                    );
                }
            }
        }
        return $this->doProcess($request);
    }

    /**
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @return \Magento\Framework\Webapi\Rest\Response
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function doProcess(\Magento\Framework\Webapi\Rest\Request $request) {
        $route = $this->inputParamsResolver->getRoute();
        $serviceMethodName = $route->getServiceMethod();
        $serviceClassName = $route->getServiceClass();
        $service = $this->objectManager->get($serviceClassName);
        $inputParams = $this->inputParamsResolver->resolve();

        // Enable Profiler
        if ($request->getParam('webposProfiler')) {
            Profiler::$enable = true;
        }

        Profiler::start('api_service');
        $outputData = call_user_func_array([$service, $serviceMethodName], $inputParams);
        Profiler::stop('api_service');

        Profiler::start('process_output');
        if (!is_array($outputData) || !isset($outputData['cached_at'])) {
            $outputData = $this->serviceOutputProcessor->process(
                $outputData,
                $serviceClassName,
                $serviceMethodName
            );
        }
        Profiler::stop('process_output');

        if ($request->getParam(FieldsFilter::FILTER_PARAMETER) && is_array($outputData)) {
            $outputData = $this->fieldsFilter->filter($outputData);
        }

        // Output profiler
        if (Profiler::$enable) {
            return $this->response->prepareResponse(Profiler::$profiles);
        }

        $header = $this->deploymentConfig->get(\Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_X_FRAME_OPT);
        if ($header) {
            $this->response->setHeader('X-Frame-Options', $header);
        }
        return $this->response->prepareResponse($outputData);
    }


    /**
     * {@inheritdoc}
     */
    public function canProcess(\Magento\Framework\Webapi\Rest\Request $request)
    {
        $route = $this->inputParamsResolver->getRoute();
        $aclResource = $route->getAclResources();
        if (in_array(self::MANAGE_POS_RESOURCE, $aclResource)) {
            return true;
        }
        return false;
    }


    /**
     * Logout customer
     */
    public function logoutCustomer()
    {
        $isLoggedInCustomer = $this->objectManager->get('Magento\Customer\Model\Session')
            ->isLoggedIn();
        if ($isLoggedInCustomer) {
            $this->objectManager->get('Magento\Customer\Model\Session')->logout();
        }
    }

    /**
     * @param string $aclResources
     * @param int $staffId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAllowWebPosPermission($aclResources, $staffId)
    {
        $route = $this->inputParamsResolver->getRoute();
        if ($route->getServiceClass() == 'Magestore\Webpos\Api\Staff\StaffManagementInterface' && $route->getServiceMethod() == 'logout') {
            return true;
        }
        $allWebPosPermission = $this->sessionRepository
            ->getAllCurrentPermission($staffId);
        if (in_array('Magestore_Appadmin::all', $allWebPosPermission)) {
            return true;
        }
        foreach ($aclResources as $resource) {
            if (!in_array($resource, $allWebPosPermission)) {
                return false;
            }
        }
        return true;
    }
}
