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
            $this->_options = array();
            foreach(Mage::getConfig()->getNode('global/carriers/yellowcube/ean/type')->asArray() as $key => $elements)
            {
                $this->_options[] =
                    array(
                        'label' => Mage::helper('swisspost_yellowcube')->__($elements['label']),
                        'value' => strtoupper($key)
                    );
            }
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




