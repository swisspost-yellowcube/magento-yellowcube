<?php

interface Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorInterface
{
    /**
     * @param array $data
     * @return void
     */
    public function process(array $data);
}
