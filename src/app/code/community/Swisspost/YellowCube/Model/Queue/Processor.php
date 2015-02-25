<?php

/**
 * Class Swisspost_YellowCube_Model_Queue_Processor
 */
class Swisspost_YellowCube_Model_Queue_Processor
{
    /**
     * Called by cron task
     *
     * @throws Exception
     * @throws Zend_Json_Exception
     * @throws Zend_Queue_Exception
     */
    public function process()
    {
        /** @var Zend_Queue $queue */
        $queue = Mage::getModel('swisspost_yellowcube/queue')->getInstance();
        foreach ($queue->receive(100) as $message) {

            if (Mage::helper('swisspost_yellowcube')->getDebug()) {
                Mage::log($message->body, Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE);
            }

            try {
                Mage::getSingleton('swisspost_yellowcube/queue_message_handler')->process(
                    Zend_Json::decode($message->body)
                );
            } catch (Exception $e) {
                Mage::logException($e);
            }

            $queue->deleteMessage($message);
        }
    }
}
