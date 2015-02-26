<?php

abstract class Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorAbstract
{
    /**
     * @var Zend_Queue
     */
    protected $_queue;

    /**
     * @return \YellowCube\Service
     */
    public function getYellowCubeService()
    {
        return Mage::getModel('swisspost_yellowcube/library_client')->getService();
    }

    /**
     * @param float $number
     * @return string
     */
    public function formatUom($number)
    {
        return number_format($number, 3, '.', '');
    }

    /**
     * @param $description
     * @return string
     */
    public function formatDescription($description)
    {
        return mb_strcut($description, 0, 40);
    }

    /**
     * @param string $sku
     * @return string
     */
    public function formatSku($sku)
    {
        return str_replace(' ', '_', $sku);
    }

    /**
     * @param $string
     * @return string
     */
    public function cutString($string, $length = 35)
    {
        return mb_strcut($string, 0, $length);
    }

    /**
     * @return Zend_Queue
     */
    public function getQueue()
    {
        if (null === $this->_queue) {
            $this->_queue = Mage::getModel('swisspost_yellowcube/queue')->getInstance();
        }
        return $this->_queue;
    }

    /**
     * @param $elem
     * @param $array
     * @return bool
     */
    public function inMultiArray($elem, $array)
    {
        foreach ($array as $key => $value) {
            if ($value == $elem) {
                return true;
            } elseif (is_array($value)) {
                if ($this->inMultiArray($elem, $value))
                    return true;
            }
        }

        return false;
    }

    /**
     * @return Swisspost_YellowCube_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('swisspost_yellowcube');
    }
}
