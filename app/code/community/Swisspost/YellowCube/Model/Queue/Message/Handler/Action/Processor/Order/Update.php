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
        $helper = Mage::helper('swisspost_yellowcube');
        $helperTools = Mage::helper('swisspost_yellowcube/tools');

        $shipment = Mage::getModel('sales/order_shipment')->load($data['order_id'], 'order_id');
        $response = $this->getYellowCubeService()->getYCCustomerOrderStatus($data['yc_reference']);

        try {
            if (!is_object($response) || !$response->isSuccess()) {
                $message = $helper->__('Order #%s Status with YellowCube Transaction ID could not get from YellowCube: "%s".', $data['order_id'], $data['yc_reference'], $response->getStatusText());
                $shipment
                    ->addComment($message, false, false)
                    ->save();

                Mage::log($message . "\n" . print_r($response, true), Zend_Log::ERR, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);
                $helperTools->sendAdminNotification($message);

                if (empty($data['try'])) {
                    $data['try'] = 0;
                }

                if (isset($data['try']) && $data['try'] < self::MAXTRIES) {
                    // Add again in the queue to have an up to date status
                    $this->getQueue()->send(Zend_Json::encode(array(
                        'action' => Swisspost_YellowCube_Model_Synchronizer::SYNC_ORDER_UPDATE,
                        'order_id' => $data['order_id'],
                        'yc_reference' => $data['yc_reference'],
                        'items' => $data['items'],
                        'try' => $data['try']++
                    )));
                }
            } else {
                if (Mage::helper('swisspost_yellowcube')->getDebug()) {
                    Mage::log(print_r($response, true), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE);
                }

                // find orders that have been processed
                if ($response->isSuccess() && !$response->isPending() && !$response->isError()) {

                    $goodsIssueList = $this->getYellowCubeService()->getYCCustomerOrderReply($data['shipment_increment_id']); // we replaced order id by shipment increment id
                    $shippingUrl = '';

                    foreach ($goodsIssueList as $goodsIssue) {
                        $header = $goodsIssue->getCustomerOrderHeader();
                        $shipmentNo = $header->getPostalShipmentNo();

                        // Multi packaging / shipping is not supported atm.
                        if (!empty($shipmentNo)) {
                            //shipping number contains a semicolon, post api supports multiple values
                            $shippingUrl = 'http://www.post.ch/swisspost-tracking?formattedParcelCodes=' . $shipmentNo;
                            break;
                        }
                    }

                    if (Mage::helper('swisspost_yellowcube')->getDebug()) {
                        Mage::log(print_r($goodsIssueList, true), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);
                    }

                    if (!empty($goodsIssueList)) {

                        Mage::log($helper->__('Goods issue list has been found.'), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);

                        /**
                         * Define yc_shipped to 1 to be used later in BAR process that the shipping has been done
                         */
                        foreach ($shipment->getItemsCollection() as $item) {
                            /* @var $item Mage_Sales_Model_Order_Shipment_Item */
                            if ($this->inMultiArray($item->getProductId(), $data['items'])) {
                                $item
                                    ->setAdditionalData(Zend_Json::encode(array('yc_shipped' => 1)))
                                    ->save();
                            }
                        }

                        // Add a message to the order history incl. link to shipping infos
                        $message = $helper->__('Your order has been shipped. You can use the following url for shipping tracking: <a href="%1$s" target="_blank">%1$s</a>', $shippingUrl);
                        $shipment
                            ->addComment($helper->__($message), true, true)
                            ->save();

                        $shipment->sendEmail(true, $message);
                    } else {
                        Mage::log($helper->__('Goods issue list is emtpy.'), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);
                    }
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
            // Let's keep going further processes

            if (isset($data['try']) && $data['try'] < self::MAXTRIES) {
                // Add again in the queue to have an up to date status
                $this->getQueue()->send(Zend_Json::encode(array(
                    'action' => Swisspost_YellowCube_Model_Synchronizer::SYNC_ORDER_UPDATE,
                    'order_id' => $data['order_id'],
                    'items' => $data['items'],
                    'yc_reference' => $data['yc_reference'],
                    'try' => $data['try']++
                )));
            }
        }

        return $this;
    }
}