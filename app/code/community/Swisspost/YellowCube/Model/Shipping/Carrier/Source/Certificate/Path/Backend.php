<?php
/**
 * Liip AG
 *
 * @author      Sylvain Rayé <sylvain.raye at diglin.com>
 * @category    yellowcube
 * @package     Swisspost_yellowcube
 * @copyright   Copyright (c) 2015 Liip AG
 */

class Swisspost_YellowCube_Model_Shipping_Carrier_Source_Certificate_Path_Backend extends Mage_Core_Model_Config_Data
{
    /**
     * Checks if the certificate is available and readable.
     *
     * @return Mage_Core_Model_Abstract
     * @throws Exception
     */
    protected function _beforeSave()
    {
        $filePath = $this->getValue();
        if (!empty($filePath) && (!file_exists($filePath) || !is_readable($filePath))) {
            throw new Exception(
                Mage::helper('swisspost_yellowcube')
                    ->__("Failed to load certificate from '%s'", $filePath)
            );
        }

        return parent::_beforeSave();
    }
}
