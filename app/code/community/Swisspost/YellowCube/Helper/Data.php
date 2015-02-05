<?php

/**
 * Class Swisspost_YellowCube_Helper_Data
 */
class Swisspost_YellowCube_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_SENDER_ID          = 'carriers/swisspost_yellowcube/sender_id';
    const CONFIG_ENDPOINT           = 'carriers/swisspost_yellowcube/soap_url';
    const CONFIG_PARTNER_NUMBER     = 'carriers/swisspost_yellowcube/patner_number';
    const CONFIG_DEPOSITOR_NUMBER   = 'carriers/swisspost_yellowcube/depositor_number';
    const CONFIG_PLANT_ID           = 'carriers/swisspost_yellowcube/plant_id';
    const CONFIG_CERT_PATH          = 'carriers/swisspost_yellowcube/certificate_path';
    const CONFIG_CERT_PASSWORD      = 'carriers/swisspost_yellowcube/certificate_password';
    const CONFIG_TARA_FACTOR        = 'carriers/swisspost_yellowcube/tara_factor';
    const CONFIG_OPERATION_MODE     = 'carriers/swisspost_yellowcube/operation_mode';

    /**
     * Get Sender Id
     *
     * @return string
     */
    public function getSenderId()
    {
        return (string) Mage::getStoreConfig(self::CONFIG_SENDER_ID);
    }

    /**
     * Get Soap Url Endpoint
     *
     * @return string
     */
    public function getEndpoint()
    {
        return (string) Mage::getStoreConfig(self::CONFIG_ENDPOINT);
    }

    /**
     * Get
     *
     * @return string
     */
    public function getPartnerNumber()
    {
        return (string) Mage::getStoreConfig(self::CONFIG_PARTNER_NUMBER);
    }

    /**
     * Get Depositor Number
     *
     * @return string
     */
    public function getDepositorNumber()
    {
        return (string) Mage::getStoreConfig(self::CONFIG_DEPOSITOR_NUMBER);
    }

    /**
     * Get Plant ID
     *
     * @return string
     */
    public function getPlantId()
    {
        return (string) Mage::getStoreConfig(self::CONFIG_PLANT_ID);
    }

    /**
     * Get certificate Path
     *
     * @return string
     */
    public function getCertificatePath()
    {
        return (string) Mage::getStoreConfig(self::CONFIG_CERT_PATH);
    }

    /**
     * Get certificate password
     *
     * @return string
     */
    public function getCertificatePassword()
    {
        return (string) Mage::getStoreConfig(self::CONFIG_CERT_PASSWORD);
    }

    /**
     * Get Tara Factor (net weight * tara factor = brut weight)
     *
     * @return float
     */
    public function getTaraFactor()
    {
        return (float) Mage::getStoreConfig(self::CONFIG_TARA_FACTOR);
    }

    /**
     * Get Operation mode P = Production, D = Development, T = Test
     *
     * @return string
     */
    public function getOperationMode()
    {
        return (string) Mage::getStoreConfig(self::CONFIG_OPERATION_MODE, 'T');
    }
}
