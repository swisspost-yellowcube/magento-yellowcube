<?php

class Swisspost_YellowCube_Adminhtml_YellowCube_System_Config_SyncController extends Mage_Adminhtml_Controller_Action
{
    public function downloadAction()
    {
        try {
            $this->getSynchronizer()->updateAll();
            Mage::log('download');
            echo 1;
        } catch (Exception $e) {
            Mage::logException($e);
            echo 0;
        }
    }

    public function uploadAction()
    {
        try {
            $this->getSynchronizer()->updateAll();
            Mage::log('upload');
            echo 1;
        } catch (Exception $e) {
            Mage::logException($e);
            echo 0;
        }
    }

    /**
     * @return Swisspost_YellowCube_Model_Synchronizer
     */
    public function getSynchronizer()
    {
        return Mage::getSingleton('swisspost_yellowcube/synchronizer');
    }
}
