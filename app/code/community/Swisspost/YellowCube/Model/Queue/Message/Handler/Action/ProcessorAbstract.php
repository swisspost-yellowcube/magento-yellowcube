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
}
