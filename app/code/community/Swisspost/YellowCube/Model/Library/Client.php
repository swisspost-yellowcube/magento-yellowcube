<?php

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
        if (true) {
            return YellowCube\Config::testConfig();
        }

//        $config = new YellowCube\Config(
//            variable_get('yellowcube_sender', ''),
//            variable_get('yellowcube_endpoint', ''),
//            null,
//            variable_get('yellowcube_mode', 'T')
//        );
//
//        //Certificate handling
//        if(in_array($operation_mode, array('P', 'D'))) {
//            if (!empty($cert_path) && file_exists($cert_path)) {
//                $config->setCertificateFilePath($cert_path);
//                //Todo: handle password
//            }
//        }
//
//        return $config;
    }
}
