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
     * Process YC WAR
     *
     * @param array $data
     * @return $this
     */
    public function process(array $data)
    {
        $helperTools = Mage::helper('swisspost_yellowcube/tools');

        $shipment = Mage::getModel('sales/order_shipment')->load($data['order_id'], 'order_id');
        $response = $this->getYellowCubeService()->getYCCustomerOrderStatus($data['yc_reference']);

        try {
            if (!is_object($response) || !$response->isSuccess()) {
                $message = $this->getHelper()->__('Order #%s Status with YellowCube Transaction ID could not get from YellowCube: "%s".',
                    $data['order_id'], $data['yc_reference'], $response->getStatusText());

                $shipment
                    ->addComment($message, false, false)
                    ->save();

                Mage::log($message . "\n" . print_r($response, true), Zend_Log::ERR, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);
                $helperTools->sendAdminNotification($message);

                if (empty($data['try'])) {
                    $data['try'] = 0;
                }

                if ($data['try'] < self::MAXTRIES) {
                    // Add again in the queue to have an up to date status
                    $this->getQueue()->send(Zend_Json::encode(array(
                        'action'                => Swisspost_YellowCube_Model_Synchronizer::SYNC_ORDER_UPDATE,
                        'order_id'              => $data['order_id'],
                        'shipment_increment_id' => $data['shipment_increment_id'],
                        'yc_reference'          => $data['yc_reference'],
                        'items'                 => $data['items'],
                        'try'                   => $data['try']++
                    )));
                }
            } else {
                if ($response->isSuccess() && !$response->isPending() && !$response->isError()) {
                    $shipment
                        ->addComment($this->getHelper()->__('Order status for YellowCube and the order %s is successful', $data['order_id']), false, false)
                        ->save();
                }

                if ($this->getHelper()->getDebug()) {
                    Mage::log(print_r($response, true), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE);
                }
            }
        } catch (Exception $e) {
            // Let's keep going further processes
            Mage::logException($e);

            if (isset($data['try']) && $data['try'] < self::MAXTRIES) {
                // Add again in the queue to have an up to date status
                $this->getQueue()->send(Zend_Json::encode(array(
                    'action' => Swisspost_YellowCube_Model_Synchronizer::SYNC_ORDER_UPDATE,
                    'order_id' => $data['order_id'],
                    'items' => $data['items'],
                    'shipment_increment_id' => $data['shipment_increment_id'],
                    'yc_reference' => $data['yc_reference'],
                    'try' => $data['try']++
                )));
            }
        }

        return $this;
    }
}