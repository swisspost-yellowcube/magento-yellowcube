<?php

/**
 * Liip
 *
 * @author      Sylvain Rayé <sylvain.raye at diglin.com>
 * @category    yellowcube
 * @package     Swisspost_yellowcube
 * @copyright   Copyright (c) 2015 Liip
 */

use \YellowCube\WAB\Partner;
use \YellowCube\WAB\Order;
use \YellowCube\WAB\OrderHeader;
use \YellowCube\WAB\Position;
use \YellowCube\WAB\AdditionalService\BasicShippingServices;

/**
 * Class Swisspost_YellowCube_Model_Queue_Message_Handler_Action_Processor_Order
 */
class Swisspost_YellowCube_Model_Queue_Message_Handler_Action_Processor_Order
    extends Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorAbstract
    implements Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorInterface
{
    public function process(array $data)
    {
        // @todo cut length of strings following Die Post API constrain
        $partner = new Partner();
        $partner
            ->setPartnerType($data['partner_type'])
            ->setPartnerNo($data['partner_number'])
            ->setPartnerReference('Liip AG')// @todo do we have to keep Liip AG here?
            ->setName1($data['partner_name'])
            ->setStreet($data['partner_street'])
            ->setCountryCode($data['partner_country_code'])
            ->setZIPCode($data['partner_zip_code'])
            ->setCity($data['partner_city'])
            ->setEmail($data['partner_email'])
            ->setPhoneNo($data['partner_phone'])
            ->setLanguageCode($data['partner_language']);

        $ycOrder = new Order();
        $ycOrder
            ->setOrderHeader(new OrderHeader($data['deposit_number'], $data['order_id'], $data['order_date']))
            ->setPartnerAddress($partner)
            ->addValueAddedService(new BasicShippingServices($data['service_basic_shipping']))
            ->setOrderDocumentsFlag(false);

        foreach ($data['items'] as $key => $item) {
            $position = new Position();
            $position
                ->setPosNo($key + 1)
                ->setArticleNo($item['article_number'])
                ->setPlant($item['plant_id'])
                ->setQuantity($item['article_qty'])
                ->setQuantityISO(\YellowCube\ART\UnitsOfMeasure\ISO::PCE)
                ->setShortDescription($item['article_title']);

            $ycOrder->addOrderPosition($position);
        }

        Mage::log($ycOrder, Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);

        // @todo uncomment and handle error
        //$response = $this->getYellowCubeService()->createYCCustomerOrder($ycOrder);
    }
}