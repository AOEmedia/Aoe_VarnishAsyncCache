<?php
/**
 * Custom url resource collection model class
 *
 * @author Dmytro Zavalkin <dmytro.zavalkin@aoemedia.de>
 */
class Aoe_VarnishAsyncCache_Model_Resource_BlacklistUrlPattern_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('varnishasynccache/blacklistUrlPattern');
    }
}
