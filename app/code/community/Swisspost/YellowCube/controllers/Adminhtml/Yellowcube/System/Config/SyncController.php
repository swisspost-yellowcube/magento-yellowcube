<?php

class Swisspost_YellowCube_Adminhtml_YellowCube_System_Config_SyncController extends Mage_Adminhtml_Controller_Action
{
    public function barAction()
    {
        try {
            $this->getSynchronizer()->bar();
            Mage::log('YellowCube: Stock data update requested by admin.');
            echo 1;
        } catch (Exception $e) {
            Mage::logException($e);
            echo 0;
        }
    }

    public function artAction()
    {
        try {
            $this->getSynchronizer()->updateAll();
            Mage::log('YellowCube: Product data update requested by admin.');
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
