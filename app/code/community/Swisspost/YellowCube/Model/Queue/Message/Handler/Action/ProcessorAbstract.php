<?php

abstract class Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorAbstract
{
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
}
