<?php
/**
 * Custom url resource model class
 *
 * @author Dmytro Zavalkin <dmytro.zavalkin@aoemedia.de>
 */
class Aoe_VarnishAsyncCache_Model_Resource_BlacklistUrlPattern extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('varnishasynccache/blacklist_url_pattern', 'pattern_id');
    }

    /**
     * Initialize unique field
     *
     * @return $this
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(
            array(
                 'field' => array('pattern'),
                 'title' => Mage::helper('varnishasynccache')->__('Blacklist Url Pattern'),
            )
        );

        return $this;
    }
}
