<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Api\Data\Integration\Giftcard;

/**
 * Interface GiftcardInterface
 * @package Magestore\Webpos\Api\Data\Integration\Giftcard
 */
interface GiftcardInterface
{
    /*#@+
     * Constants defined for keys of data array
     */
    const CODE = "code";
    const BALANCE = "balance";
    const CURRENCY = "currency";
    const VALID_ITEM_IDS = 'valid_item_ids';

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     * @return GiftcardInterface
     */
    public function setCode($code);

    /**
     * @return float
     */
    public function getBalance();

    /**
     * @param float $balance
     * @return GiftcardInterface
     */
    public function setBalance($balance);
    
    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @param string $currency
     * @return GiftcardInterface
     */
    public function setCurrency($currency);

    /**
     * Get ValidItemIds
     *
     * @return float[]|null
     */
    public function getValidItemIds();
    /**
     * Set ValidItemIds
     *
     * @param float[]|null $validItemIds
     * @return GiftcardInterface
     */
    public function setValidItemIds($validItemIds);
}