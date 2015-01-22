<?php

class Swisspost_YellowCube_Model_Queue_Message_Handler_Action_Processor_InventorySync
    extends Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorAbstract
    implements Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorInterface
{
    /**
     * @param array $data
     * @throws Exception
     */
    public function process(array $data)
    {
        throw new Exception(__METHOD__ . ' is not implemented');
    }
}
