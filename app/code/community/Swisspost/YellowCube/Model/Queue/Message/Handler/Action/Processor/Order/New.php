<?php

/**
 * Liip AG
 *
 * @author      Sylvain Rayé <sylvain.raye at diglin.com>
 * @category    yellowcube
 * @package     Swisspost_yellowcube
 * @copyright   Copyright (c) 2015 Liip AG
 */

use \YellowCube\WAB\Partner;
use \YellowCube\WAB\Order;
use \YellowCube\WAB\OrderHeader;
use \YellowCube\WAB\Position;
use \YellowCube\WAB\AdditionalService\BasicShippingServices;

/**
 * Class Swisspost_YellowCube_Model_Queue_Message_Handler_Action_Processor_Order
 */
class Swisspost_YellowCube_Model_Queue_Message_Handler_Action_Processor_Order_New
    extends Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorAbstract
    implements Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorInterface
{
    public function process(array $data)
    {
        $helper = Mage::helper('swisspost_yellowcube');
        $helperTools = Mage::helper('swisspost_yellowcube/tools');

        $partner = new Partner();
        $partner
            ->setPartnerType($data['partner_type'])
            ->setPartnerNo($this->cutString($data['partner_number']), 10)
            ->setPartnerReference($this->cutString('Liip AG - Magento'), 50)// @todo do we have to keep Liip AG here?
            ->setName1($this->cutString($data['partner_name']))
            ->setStreet($this->cutString($data['partner_street']))
            ->setCountryCode($data['partner_country_code'])
            ->setZIPCode($this->cutString($data['partner_zip_code']), 10)
            ->setCity($this->cutString($data['partner_city']))
            ->setEmail($this->cutString($data['partner_email']), 241)
            ->setPhoneNo($this->cutString($data['partner_phone']), 16)
            ->setLanguageCode($this->cutString($data['partner_language']), 2);

        $ycOrder = new Order();
        $ycOrder
            ->setOrderHeader(new OrderHeader($this->cutString($data['deposit_number'], 10), $this->cutString($data['order_id']), $data['order_date']))
            ->setPartnerAddress($partner)
            ->addValueAddedService(new BasicShippingServices($this->cutString($data['service_basic_shipping']), 40))
            ->setOrderDocumentsFlag(false);

        foreach ($data['items'] as $key => $item) {
            $position = new Position();
            $position
                ->setPosNo($key + 1)
                ->setArticleNo($this->cutString($item['article_number']))
                ->setPlant($this->cutString($data['plant_id']), 4)
                ->setQuantity($item['article_qty'])
                ->setQuantityISO(\YellowCube\ART\UnitsOfMeasure\ISO::PCE)
                ->setShortDescription($this->cutString($item['article_title']), 40);

            $ycOrder->addOrderPosition($position);
        }

        $response = $this->getYellowCubeService()->createYCCustomerOrder($ycOrder);
        $shipment = Mage::getModel('sales/order_shipment')->load($data['order_id'], 'order_id');

        try {
            if (!is_object($response) || !$response->isSuccess()) {
                $message = $helper->__('Order #%s could not be transmitted to YellowCube: "%s".', $data['order_id'], $response->getStatusText());

                $shipment
                    ->addComment($message, false, false)
                    ->save();

                Mage::log($message . "\n" . print_r($response, true), Zend_Log::ERR, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);
                $helperTools->sendAdminNotification($message);

                // @todo allow the user to send again to yellow cube the request from backend

            } else {
                if (Mage::helper('swisspost_yellowcube')->getDebug()) {
                    Mage::log(print_r($ycOrder, true), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE);
                    Mage::log(print_r($response, true), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE);
                }

                /**
                 * Define yc_shipped to 0 to be used later in BAR process that the shipping has not been done
                 */
                reset($data['items']);
                foreach ($shipment->getItemsCollection() as $item) {
                    /* @var $item Mage_Sales_Model_Order_Shipment_Item */
                    if ($this->inMultiArray($item->getProductId(), $data['items'])) {
                        $item
                            ->setAdditionalData(Zend_Json::encode(array('yc_shipped' => 0)))
                            ->save();
                    }
                }

                $shipment
                    ->addComment($helper->__('Order #%s was successfully transmitted to YellowCube. Received reference number %s and status message "%s".', $data['order_id'], $response->getReference(), $response->getStatusText()), false, false)
                    ->save();

                // WAR Message
                $this->getQueue()->send(Zend_Json::encode(array(
                    'action' => Swisspost_YellowCube_Model_Synchronizer::SYNC_ORDER_UPDATE,
                    'order_id' => $data['order_id'],
                    'yc_reference' => $response->getReference()
                )));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            // Let's keep going further processes
        }

        return $this;
    }
}