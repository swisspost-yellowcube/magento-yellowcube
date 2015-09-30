<?php

use Psr\Log\LogLevel;
use YellowCube\Service;
use YellowCube\Config;
use YellowCube\Util\Logger\MinLevelFilterLogger;

/**
 * Class Swisspost_YellowCube_Model_Library_Client
 */
class Swisspost_YellowCube_Model_Library_Client
{
    /**
     * @return \YellowCube\Service
     */
    public function getService()
    {
        $logger = new MinLevelFilterLogger(LogLevel::DEBUG, new Swisspost_YellowCube_Model_Library_Logger());
        return new YellowCube\Service($this->getServiceConfig(), null, $logger);
    }

    /**
     * @return \YellowCube\Config
     */
    public function getServiceConfig()
    {
        $helper = Mage::helper('swisspost_yellowcube');

        $certificatePath = $helper->getCertificatePath();
        $certificatePassword = $helper->getCertificatePassword();

        if (!$helper->isConfigured()) {
            Mage::throwException(
                $helper->__('YellowCube Extension is not properly configured. Please <a href="%s">configure</a> it before to continue.',
                    Mage::getUrl('system_config/edit/section/carriers')));
        }

        $config = new YellowCube\Config(
            $helper->getSenderId(),
            $helper->getEndpoint(),
            null,
            $helper->getOperationMode()
        );

        // Certificate handling
        if (in_array($helper->getOperationMode(), array('P', 'T', 'D'))) {
            if (!empty($certificatePath)) {
                $config->setCertificateFilePath($certificatePath, $certificatePassword);
            }
        }

        return $config;
    }
}
