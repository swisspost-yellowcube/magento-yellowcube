<?php

class Swisspost_YellowCube_Model_Shipping_Carrier_Rate extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * @var string
     */
    protected $_code = 'yellowcube';

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

        foreach ($this->getAllowedMethods() as $methodCode => $methodName) {
            /** @var Mage_Shipping_Model_Rate_Result_Method $method */
            $method = Mage::getModel('shipping/rate_result_method');
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod($methodCode);
            $method->setMethodTitle($methodName);
            $method->setPrice($this->getPriceMethod($methodCode));
            $method->setCost(0);

            $result->append($method);
        }

        return $result;
    }

    /**
     * Get the price depending the method
     *
     * @param $code
     * @return int
     */
    public function getPriceMethod($code)
    {
        $allowedMethods = unserialize($this->getConfigData('allowed_methods'));

        foreach ($allowedMethods as $method) {
            if ($method['allowed_methods'] == $code) {
                return $method['price'];
            }
        }
        return 0;
    }

    /**
     * Get allowed Methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $methods = Mage::getSingleton('swisspost_yellowcube/shipping_carrier_source_method')->getMethods();
        $allowedMethods = unserialize($this->getConfigData('allowed_methods'));

        $allowed = array();
        foreach ($allowedMethods as $method) {
            $allowed[$method['allowed_methods']] = $method['allowed_methods'];
        }

        $arr = array();
        foreach ($methods as $key => $method) {
            /* @var $method Mage_Core_Model_Config_Element */
            if (array_key_exists($key, $allowed)) {
                $arr[$key] = $methods[$key];
            }
        };

        return $arr;
    }

    /**
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @return Varien_Object
     */
    public function requestToShipment(Mage_Shipping_Model_Shipment_Request $request)
    {
        // No error is returned as it is an asynchron process with yellowcube
        Mage::getSingleton('swisspost_yellowcube/synchronizer')->ship($request);

        return new Varien_Object();
    }
}
