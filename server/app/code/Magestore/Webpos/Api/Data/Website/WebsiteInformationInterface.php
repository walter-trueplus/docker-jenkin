<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Api\Data\Website;

/**
 * Interface WebsiteInformationInterface
 * @package Magestore\Webpos\Api\Data\Website
 */
interface WebsiteInformationInterface
{
    /**
     *
     */
    const LOGO_URL = "logo_url";

    /**
     * Get logo url
     *
     * @api
     * @return string
     */
    public function getLogoUrl();

    /**
     * Set logo url
     *
     * @api
     * @param string $logoUrl
     * @return WebsiteInformationInterface
     */
    public function setLogoUrl($logoUrl);

}
