<?php

class Swisspost_YellowCube_Model_Queue_Processor
{
    public function process()
    {
        /** @var Zend_Queue $queue */
        $queue = Mage::getModel('swisspost_yellowcube/queue')->getInstance();
        foreach ($queue->receive(100) as $message) {
            Mage::getSingleton('swisspost_yellowcube/queue_message_handler')->process(
                Zend_Json::decode($message->body)
            );
            $queue->deleteMessage($message);
        }
    }
}
