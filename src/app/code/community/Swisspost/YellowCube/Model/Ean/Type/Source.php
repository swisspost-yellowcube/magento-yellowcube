<?php

class Swisspost_YellowCube_Model_Ean_Type_Source extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{


    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => Mage::helper('swisspost_yellowcube')->__('HE'),
                    'value' => 'HE'
                ),
                array(
                    'label' => Mage::helper('swisspost_yellowcube')->__('E5'),
                    'value' => 'E5'
                ),
                array(
                    'label' => Mage::helper('swisspost_yellowcube')->__('EA'),
                    'value' => 'EA'
                ),
                array(
                    'label' => Mage::helper('swisspost_yellowcube')->__('HK'),
                    'value' => 'HK'
                ),
                array(
                    'label' => Mage::helper('swisspost_yellowcube')->__('I6'),
                    'value' => 'I6'
                ),
                array(
                    'label' => Mage::helper('swisspost_yellowcube')->__('IC'),
                    'value' => 'IC'
                ),
                array(
                    'label' => Mage::helper('swisspost_yellowcube')->__('IE'),
                    'value' => 'IE'
                ),
                array(
                    'label' => Mage::helper('swisspost_yellowcube')->__('IK'),
                    'value' => 'IK'
                ),
                array(
                    'label' => Mage::helper('swisspost_yellowcube')->__('SA'),
                    'value' => 'SA'
                ),
                array(
                    'label' => Mage::helper('swisspost_yellowcube')->__('SG'),
                    'value' => 'SG'
                ),
                array(
                    'label' => Mage::helper('swisspost_yellowcube')->__('UC'),
                    'value' => 'UC'
                ),
                array(
                    'label' => Mage::helper('swisspost_yellowcube')->__('VC'),
                    'value' => 'VC'
                ),
            );
        }
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}




