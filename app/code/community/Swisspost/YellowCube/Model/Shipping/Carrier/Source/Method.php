<?php

class Swisspost_YellowCube_Model_Shipping_Carrier_Source_Method
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $arr = array();
        foreach ($this->getMethods() as $key => $value) {
            $arr[] = array('value' => $key, 'label' => $value);
        }
        return $arr;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        $result = array();
        foreach(Mage::getConfig()->getNode('global/carriers/swisspost_yellowcube/methods')->asArray() as $methodData) {
            if (!isset($methodData['code']) || !isset($methodData['label'])) {
                continue;
            }
            $result[$methodData['code']] = $methodData['label'];
        }

        return $result;
    }
}
