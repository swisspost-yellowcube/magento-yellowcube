<?php
/**
 * Liip AG
 *
 * @author      Sylvain Rayé <sylvain.raye at diglin.com>
 * @category    yellowcube
 * @package     Swisspost_yellowcube
 * @copyright   Copyright (c) 2015 Liip AG
 */

/**
 * Class Swisspost_YellowCube_Model_Cron
 */
class Swisspost_YellowCube_Model_Cron
{
    /**
     * Add a message to the queue to sync the YellowCube Inventory with Magento Products
     */
    public function inventory()
    {
        Mage::getModel('swisspost_yellowcube/queue')
            ->getInstance()
            ->send(Zend_Json::encode(array(
            'action' => Swisspost_YellowCube_Model_Synchronizer::SYNC_INVENTORY
        )));

        return $this;
    }
}