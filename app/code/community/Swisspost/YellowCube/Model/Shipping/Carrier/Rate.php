<?php

class Swisspost_YellowCube_Model_Shipping_Carrier_Rate extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * @var string
     */
    protected $_code = 'swisspost_yellowcube';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool|Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');
        $methodPrice = (int)$this->getConfigData('handling_fee');

        foreach ($this->getAllowedMethods() as $methodCode => $methodName) {
            /** @var Mage_Shipping_Model_Rate_Result_Method $method */
            $method = Mage::getModel('shipping/rate_result_method');
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod($methodCode);
            $method->setMethodTitle($methodName);
            $method->setPrice($methodPrice);
            $method->setCost(0);

            $result->append($method);
        }

        return $result;
    }

    public function getAllowedMethods()
    {
        /** @var Swisspost_YellowCube_Model_Shipping_Carrier_Source_Method $method */
        $method = Mage::getSingleton('swisspost_yellowcube/shipping_carrier_source_method');
        $methods = $method->getMethods();

        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = array();
        foreach ($allowed as $k) {
            $arr[$k] = $methods[$k];
        }
        return $arr;
    }

}
