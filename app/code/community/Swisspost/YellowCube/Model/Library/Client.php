<?php

use YellowCube\Service;
use YellowCube\Config;

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
        return new YellowCube\Service($this->getServiceConfig());
    }

    /**
     * @return \YellowCube\Config
     */
    public function getServiceConfig()
    {
        $helper = Mage::helper('swisspost_yellowcube');

//        $senderId = $helper->getSenderId();
//        $endpoint = $helper->getEndpoint();
//        $operationMode = $helper->getOperationMode();
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
        if (in_array($helper->getOperationMode(), array('P', 'D'))) {
            if (!empty($certificatePath) && file_exists($certificatePath)) {
                $config->setCertificateFilePath($certificatePath, $certificatePassword);
            }
        }

        return $config;
    }
}
