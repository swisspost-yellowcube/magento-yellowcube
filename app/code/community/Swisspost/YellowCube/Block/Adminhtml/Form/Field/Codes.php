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
 * Class Swisspost_Yellowcube_Block_Adminhtml_Form_Field_Codes
 */
class Swisspost_Yellowcube_Block_Adminhtml_Form_Field_Codes extends Mage_Core_Block_Html_Select
{
    /**
     * Carrier Code
     *
     * @var array
     */
    private $_codes;

    /**
     * Retrieve allowed carrier codes
     *
     * @param int $code
     * @return array|string
     */
    protected function _getCarrierCodes($code = null)
    {
        if (is_null($this->_codes)) {
            $this->_codes = array();
            $codes = Mage::getConfig()->getNode('global/carriers/yellowcube/methods')->asArray();

            foreach ($codes as $key => $item) {
                /* @var $item Mage_Customer_Model_Group */
                $this->_codes[$item['code']] = $item['label'];
            }
        }
        if (!is_null($code)) {
            return isset($this->_codes[$code]) ? $this->_codes[$code] : null;
        }
        return $this->_codes;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getCarrierCodes() as $code => $label) {
                $this->addOption($code, addslashes($label));
            }
        }
        return parent::_toHtml();
    }
}
