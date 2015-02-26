<?php
/**
 * Liip AG
 *
 * @author      Sylvain Rayé <sylvain.raye at diglin.com>
 * @category    Yellowcube
 * @package     Swisspost_Yellowcube
 * @copyright   Copyright (c) 2015 Liip AG
 */

class Swisspost_Yellowcube_Model_Shipping_Carrier_Source_Additional
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('swisspost_yellowcube');
        $arr = array(
            array('value' => 'SI', 'label' => $helper->__('Signature')),
            array('value' => 'AZ', 'label' => $helper->__('Evening delivery')),
            array('value' => 'SA', 'label' => $helper->__('Saturday delivery')),
            array('value' => 'APOST', 'label' => $helper->__('A-POST')),
            array('value' => 'INTPRI', 'label' => $helper->__('Priority International')),
            array('value' => 'INTECO', 'label' => $helper->__('Economy International')),
            array('value' => 'GR', 'label' => $helper->__('Gross')),
            array('value' => 'MX', 'label' => $helper->__('Maxi')),
        );

        return $arr;
    }
}
