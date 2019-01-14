<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Api\Data\Staff\Logout;

/**
 * @api
 */
/**
 * Interface LogoutResultInterface
 * @package Magestore\Webpos\Api\Data\Staff\Logout
 */
interface LogoutResultInterface
{
    /**
     *
     */
    const MESSAGE = 'message';

    /**
     * Get message
     *
     * @api
     * @return string
     */
    public function getMessage();

    /**
     * Set message
     *
     * @api
     * @param string $message
     * @return LogoutResultInterface
     */
    public function setMessage($message);


}
