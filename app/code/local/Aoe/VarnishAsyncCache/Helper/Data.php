<?php

class Aoe_VarnishAsyncCache_Helper_Data extends Magneto_Varnish_Helper_Data
{
    CONST MODE_PURGEVARNISHURL = 'purgeVarnishUrl';

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
            /** @var $asyncCache Aoe_AsyncCache_Model_Asynccache */
            $asyncCache = Mage::getModel('aoeasynccache/asynccache');
            $asyncCache->setTstamp(time())
                ->setMode(Aoe_VarnishAsyncCache_Helper_Data::MODE_PURGEVARNISHURL)
                ->setTags($url)
                ->setStatus(Aoe_AsyncCache_Model_Asynccache::STATUS_PENDING)
                ->save();
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
