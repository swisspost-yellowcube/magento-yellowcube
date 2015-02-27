<?php

/**
 * Liip AG
 *
 * @author      Sylvain Rayé <sylvain.raye at diglin.com>
 * @category    Yellowcube
 * @package     Swisspost_Yellowcube
 * @copyright   Copyright (c) 2015 Liip AG
 */
class Swisspost_Yellowcube_Model_Queue_Message_Handler_Action_Processor_War
    extends Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorAbstract
    implements Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorInterface
{
    /**
     * @param array $data
     * @return $this
     */
    public function process(array $data)
    {
        try {
            $goodsIssueList = $this->getYellowCubeService()->getYCCustomerOrderReply();

            foreach ($goodsIssueList as $goodsIssue) {
                $header = $goodsIssue->getCustomerOrderHeader();

                $shipment = Mage::getModel('sales/order_shipment')->load($header->getCustomerOrderNo(), 'increment_id');
                $shipmentNo = $header->getPostalShipmentNo();

                // Multi packaging / shipping is not supported atm.
                if (!empty($shipmentNo) && $shipment->getId()) {
                    /**
                     * Define yc_shipped to 1 to be used later in BAR process that the shipping has been done
                     */
                    $customerOrderDetails = $goodsIssue->getCustomerOrderList();
                    $shipmentItems = $shipment->getItemsCollection();
                    $hash = array();

                    try {
                        foreach ($customerOrderDetails as $customerOrderDetail) {
                            Mage::log('Debug $customerOrderDetail ' . print_r($customerOrderDetail, true), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);

                            reset($shipmentItems);
                            foreach ($shipmentItems as $item) {

                                Mage::log('Debug $item ' . print_r($item, true), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);

                                /* @var $item Mage_Sales_Model_Order_Shipment_Item */
                                if ($customerOrderDetail->getArticleNo() == $item->getSku() && !isset($hash[$item->getId()])) {
                                    $item
                                        ->setAdditionalData(Zend_Json::encode(array('yc_shipped' => 1)))
                                        ->save();
                                    $hash[$item->getId()] = true;
                                }
                            }
                        }
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }

                    Mage::log($this->getHelper()->__('Items for shipment %s considered as shipped', $shipment->getIncrementId()), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);

                    // shipping number contains a semicolon, post api supports multiple values
                    $shippingUrl = 'http://www.post.ch/swisspost-tracking?formattedParcelCodes=' . $shipmentNo;

                    // Add a message to the order history incl. link to shipping infos
                    $message = $this->getHelper()->__('Your order has been shipped. You can use the following url for shipping tracking: <a href="%1$s" target="_blank">%1$s</a>', $shippingUrl);

                    $track = Mage::getModel('sales/order_shipment_track');
                    $track
                        ->setCarrierCode($shipment->getOrder()->getShippingCarrier()->getCarrierCode())
                        ->setTitle($this->getHelper()->__('SwissPost Tracking Code'))
                        ->setNumber($shippingUrl);

                    $shipment
                        ->addTrack($track)
                        ->addComment($this->getHelper()->__($message), true, true)
                        ->save();

                    $shipment->sendEmail(true, $message);

                    Mage::log($this->getHelper()->__('Shipment %s comment added and email sent', $shipment->getIncrementId()), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);
                }
            }

            if ($this->getHelper()) {
                Mage::log(print_r($goodsIssueList, true), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);
            }
        } catch (Exception $e) {
            // Let's keep going further processes
            Mage::logException($e);
        }

        return $this;
    }
}