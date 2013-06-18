<?php
/**
 * Custom url model class
 *
 * @author Dmytro Zavalkin <dmytro.zavalkin@aoemedia.de>
 *
 * @method Aoe_VarnishAsyncCache_Model_Resource_BlacklistUrlPattern _getResource()
 * @method Aoe_VarnishAsyncCache_Model_Resource_BlacklistUrlPattern getResource()
 * @method string getPattern()
 * @method Aoe_VarnishAsyncCache_Model_BlacklistUrlPattern setPattern(string $value)
 */
class Aoe_VarnishAsyncCache_Model_BlacklistUrlPattern extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('varnishasynccache/blacklistUrlPattern');
    }

    /**
     * Validate model data
     *
     * @return array|bool
     */
    public function validate()
    {
        $errors = array();

        $pattern = $this->getData('pattern');
        if (@preg_match($pattern, '') === false) {
            $errors[] = Mage::helper('varnishasynccache')->__('Pattern is not valid PHP PCRE');
        }

        if (count($errors) == 0) {
            return true;
        } else {
            return $errors;
        }
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     * @throws Mage_Core_Exception
     */
    protected function _beforeSave()
    {
        $validationResult = $this->validate();

        if ($validationResult === true) {
            return parent::_beforeSave();
        } else {
            if (is_array($validationResult) && count($validationResult) > 0) {
                $message = array_shift($validationResult);
            } else {
                $message = Mage::helper('varnishasynccache')->__('An error occurred while saving url pattern');
            }

            throw new Mage_Core_Exception($message);
        }
    }
}
