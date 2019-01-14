<?php

namespace Magestore\Appadmin\Api\Event;

interface DispatchServiceInterface {
    const EVENT_NAME_FORCE_SIGN_OUT = 'magestore_webpos_force_sign_out';
    const EVENT_NAME_FORCE_CHANGE_POS = 'magestore_webpos_force_change_pos';

    const EXCEPTION_CODE_FORCE_SIGN_OUT = 900;
    const EXCEPTION_CODE_FORCE_CHANGE_POS = 901;
    const EXCEPTION_CODE_EXTERNAL_STOCKS = 902;
    const EXCEPTION_CODE_CANNOT_LOGIN = 903;

    const EXCEPTION_MESSAGE = 'Opps! Access denied. Recent action has not been saved yet.';
//    const EXCEPTION_MESSAGE = 'Opps! Access denied. You have been logged out.';
    const EXCEPTION_VEW_EXTERNAL_STOCK_MESSAGE = 'Opps! Access denied. You don\' have permission to view external stocks.';

    /**
     * @param int $staffId
     * @param int $posId
     * @return bool
     */
    public function dispatchEventForceSignOut($staffId, $posId = null);

    /**
     * @param int $staffId
     * @param int $posId
     * @return bool
     */
    public function dispatchEventForceChangePos($staffId, $posId);
}