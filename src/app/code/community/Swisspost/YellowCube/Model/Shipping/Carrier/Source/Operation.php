<?php
/**
 * Liip AG
 *
 * @author      Sylvain Rayé <sylvain.raye at diglin.com>
 * @category    yellowcube
 * @package     Swisspost_yellowcube
 * @copyright   Copyright (c) 2015 Liip AG
 */

/**
 * Class Swisspost_YellowCube_Model_Shipping_Carrier_Source_Operation
 */
class Swisspost_YellowCube_Model_Shipping_Carrier_Source_Operation
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('swisspost_yellowcube');
        $arr[] = array('value' => 'T', 'label' => $helper->__('Test'));
        $arr[] = array('value' => 'D', 'label' => $helper->__('Development'));
        $arr[] = array('value' => 'P', 'label' => $helper->__('Production'));

        return $arr;
    }
}