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
            $helper = Mage::helper('swisspost_yellowcube');

            foreach ($goodsIssueList as $goodsIssue) {
                $header = $goodsIssue->getCustomerOrderHeader();

                $shipment = Mage::getModel('sales/order_shipment')->load($header->getCustomerOrderNo(), 'increment_id');
                $shipmentNo = $header->getPostalShipmentNo();

                // Multi packaging / shipping is not supported atm.
                if (!empty($shipmentNo) && $shipment->getId()) {
                    /**
                     * Define yc_shipped to 1 to be used later in BAR process that the shipping has been done
                     */
                    $customerOrderList = $goodsIssue->getCustomerOrderList();
                    $shipmentItems = $shipment->getItemsCollection();
                    $hash = array();

                    foreach ($customerOrderList as $customerOrderDetail) {
                        reset($shipmentItems);
                        foreach ($shipmentItems as $item) {
                            /* @var $item Mage_Sales_Model_Order_Shipment_Item */
                            if ($customerOrderDetail->getArticleNo() == $item->getSku() && !isset($hash[$item->getId()])) {
                                $item
                                    ->setAdditionalData(Zend_Json::encode(array('yc_shipped' => 1)))
                                    ->save();
                                $hash[$item->getId()] = true;
                            }
                        }
                    }

                    Mage::log($helper->__('Items for shipment %s considered as shipped', $shipment->getIncrementId()), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);

                    // shipping number contains a semicolon, post api supports multiple values
                    $shippingUrl = 'http://www.post.ch/swisspost-tracking?formattedParcelCodes=' . $shipmentNo;

                    // Add a message to the order history incl. link to shipping infos
                    $message = $helper->__('Your order has been shipped. You can use the following url for shipping tracking: <a href="%1$s" target="_blank">%1$s</a>', $shippingUrl);
                    $shipment
                        ->addComment($helper->__($message), true, true)
                        ->save();

                    $shipment->sendEmail(true, $message);

                    Mage::log($helper->__('Shipment %s comment added and email sent', $shipment->getIncrementId()), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);
                }
            }

            if (Mage::helper('swisspost_yellowcube')->getDebug()) {
                Mage::log(print_r($goodsIssueList, true), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);
            }
        } catch (Exception $e) {
            // Let's keep going further processes
            Mage::logException($e);
        }

        return $this;
    }
}