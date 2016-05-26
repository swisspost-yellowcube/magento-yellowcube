<?php
/**
 * Liip AG
 *
 * @author      Sylvain Rayé <sylvain.raye at diglin.com>
 * @category    yellowcube
 * @package     Swisspost_yellowcube
 * @copyright   Copyright (c) 2015 Liip AG
 */

class Swisspost_YellowCube_Model_System_Config_Backend_Methods extends Mage_Core_Model_Config_Data
{
    protected $_eventPrefix = 'yellowcube_config_data';

    /**
     * Process data after load
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $this->setValue(unserialize($value));
    }

    /**
     * Prepare data before save
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        unset($value['__empty']);
        $this->setValue(serialize($value));
    }
}
