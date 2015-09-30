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
    const MODE_TESTING = 'T';
    const MODE_DEVELOPMENT = 'D';
    const MODE_PRODUCTION = 'P';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('swisspost_yellowcube');

        return array(
            array(
                'value' => self::MODE_TESTING,
                'label' => $helper->__('Test')
            ),
            array(
                'value' => self::MODE_DEVELOPMENT,
                'label' => $helper->__('Development')
            ),
            array(
                'value' => self::MODE_PRODUCTION,
                'label' => $helper->__('Production')
            )
        );
    }
}
