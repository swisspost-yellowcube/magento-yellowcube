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
 * Class Swisspost_YellowCube_Model_Queue_Message_Handler_Action_Processor_Order
 */
class Swisspost_YellowCube_Model_Queue_Message_Handler_Action_Processor_Order_Update
    extends Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorAbstract
    implements Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorInterface
{

    // 1440 = 24 hours * 5 days * (60/5 times per hour - cron job run each 5 minutes)
    const MAXTRIES = 1440;

    /**
     * Process WAB Status Update
     *
     * @param array $data
     * @return $this
     */
    public function process(array $data)
    {
        $helperTools = Mage::helper('swisspost_yellowcube/tools');

        $shipment = Mage::getModel('sales/order_shipment')->load($data['order_id'], 'order_id');
        $response = $this->getYellowCubeService()->getYCCustomerOrderStatus($data['yc_reference']);

        Mage::log(print_r($response, true),Zend_Log::INFO, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);


        try {
            if (!is_object($response) || !$response->isSuccess()) {
                $message = $this->getHelper()->__('Shipment #%s Status for Order #%s with YellowCube Transaction ID could not received from YellowCube: "%s".',
                    $shipment->getIncrementId(), $data['order_id'], $data['yc_reference'], $response->getStatusText());

                $shipment
                    ->addComment($message, false, false)
                    ->save();

                Mage::log($message . "\n" . print_r($response, true), Zend_Log::ERR, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);
                $helperTools->sendAdminNotification($message);

                $this->resendMessageToQueue($data);

            } else {
                if ($response->isSuccess() && !$response->isPending() && !$response->isError()) {
                    $shipment
                        ->addComment($this->getHelper()->__('Success ' . $response->getStatusText()), false, false)
                        ->save();
                }
                else if ($response->isError())
                {
                    $shipment
                        ->addComment($this->getHelper()->__('YellowCube Error: ' . $response->getStatusText()), false, false)
                        ->save();
                }
                else if ($response->isPending())
                {
                    $this->resendMessageToQueue($data);
                }

                if ($this->getHelper()->getDebug()) {
                    Mage::log(print_r($response, true), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE);
                }
            }
        } catch (Exception $e) {

            $shipment
                ->addComment('Error: ' . $e->getMessage(), false, false)
                ->save();
            // Let's keep going further processes
            $this->resendMessageToQueue($data);

            Mage::logException($e);
        }

        return $this;
    }

    protected function resendMessageToQueue($data)
    {
        if (empty($data['try'])) {
            $data['try'] = 1;
        }
        if (isset($data['try']) && $data['try'] < self::MAXTRIES) {
            // Add again in the queue to have an up to date status
            $this->getQueue()->send(Zend_Json::encode(array(
                'action' => Swisspost_YellowCube_Model_Synchronizer::SYNC_ORDER_UPDATE,
                'order_id' => $data['order_id'],
                'items' => $data['items'],
                'shipment_increment_id' => $data['shipment_increment_id'],
                'yc_reference' => $data['yc_reference'],
                'try' => $data['try'] + 1
            )));
        }
    }
}