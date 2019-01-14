<?php

namespace Magestore\Webpos\Api\Data\Catalog\Option;

interface SwatchOptionInterface {
    const ATTRIBUTE_ID = 'attribute_id';
    const ATTRIBUTE_CODE = 'attribute_code';
    const ATTRIBUTE_LABEL = 'attribute_label';
    const SWATCHES = 'swatches';

    /**
     * Get Attribute Id
     *
     * @return int|null
     */
    public function getAttributeId();	
    /**
     * Set Attribute Id
     *
     * @param int $attributeId
     * @return SwatchOptionInterface
     */
    public function setAttributeId($attributeId);

    /**
     * Get Attribute Code
     *
     * @return string|null
     */
    public function getAttributeCode();	
    /**
     * Set Attribute Code
     *
     * @param string $attributeCode
     * @return SwatchOptionInterface
     */
    public function setAttributeCode($attributeCode);

    /**
     * Get Attribute Label
     *
     * @return string|null
     */
    public function getAttributeLabel();	
    /**
     * Set Attribute Label
     *
     * @param string $attributeLabel
     * @return SwatchOptionInterface
     */
    public function setAttributeLabel($attributeLabel);

    /**
     * Get Swatches
     *
     * @return \Magestore\Webpos\Api\Data\Catalog\Option\Swatch\SwatchInterface[]
     */
    public function getSwatches();	
    /**
     * Set Swatches
     *
     * @param \Magestore\Webpos\Api\Data\Catalog\Option\Swatch\SwatchInterface[] $swatches
     * @return SwatchOptionInterface
     */
    public function setSwatches($swatches);
}