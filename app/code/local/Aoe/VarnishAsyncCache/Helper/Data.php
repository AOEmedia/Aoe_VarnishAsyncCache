<?php

class Aoe_VarnishAsyncCache_Helper_Data extends Magneto_Varnish_Helper_Data
{
    CONST MODE_PURGEVARNISHURL = 'purgeVarnishUrl';

    /**
     * @var string[]
     */
    protected $_blacklist;

    /**
     * Get array of blacklist url patterns
     *
     * @return string[]
     */
    public function  getBlacklist()
    {
        if ($this->_blacklist === null) {
            /** @var Aoe_VarnishAsyncCache_Model_Resource_BlacklistUrlPattern_Collection $collection */
            $collection = Mage::getResourceModel('varnishasynccache/blacklistUrlPattern_collection');
            $this->_blacklist = $collection->getColumnValues('pattern');
        }

        return $this->_blacklist;
    }

    /**
     * Store an array of urls in cache for purging with Aoe_AsyncCache extension later
     *
     * @param   string[] $urls
     * @throws  Zend_Db_Statement_Exception
     * @return  void
     */
    public function purge(array $urls)
    {
        foreach ($urls as $url) {
            $isUrlMatched = false;
            foreach ($this->getBlacklist() as $pattern) {
                if (preg_match($pattern, $url)) {
                    $isUrlMatched = true;
                    break;
                }
            }

            // purge only that urls which aren't blacklisted
            if (!$isUrlMatched) {
                /** @var $asyncCache Aoe_AsyncCache_Model_Asynccache */
                $asyncCache = Mage::getModel('aoeasynccache/asynccache');
                $asyncCache->setTstamp(time())
                    ->setMode(Aoe_VarnishAsyncCache_Helper_Data::MODE_PURGEVARNISHURL)
                    ->setTags($url)
                    ->setStatus(Aoe_AsyncCache_Model_Asynccache::STATUS_PENDING)
                    ->save();
            }
        }
    }

    /**
     * Use original purge method to finally purge all stored varnish urls
     *
     * @param   array $urls
     * @return  array with all errors
     */
    public function purgeVarnishUrls(array $urls)
    {
        return parent::purge($urls);
    }
}
