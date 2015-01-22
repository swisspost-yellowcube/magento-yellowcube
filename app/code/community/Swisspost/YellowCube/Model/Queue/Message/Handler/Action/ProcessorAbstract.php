<?php

abstract class Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorAbstract
{
    public function __construct()
    {
        $pathParts = array(Mage::getBaseDir('lib'), 'Liip', 'yellowcube-php', 'require-lib.php');
        require_once(implode(DS, $pathParts));
    }

    /**
     * @return \YellowCube\Service
     */
    public function getYellowCubeService()
    {
        //TODO: implement instantiation of service through Swisspost_YellowCube_Model_Library_Client
        return new YellowCube\Service(YellowCube\Config::testConfig());
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
