<?php

class Aoe_VarnishAsyncCache_Helper_Data extends Magneto_Varnish_Helper_Data {

    /**
     * Store an array of urls in cache for purging with Aoe_AsyncCache extension later
     *
     * @param   array   $urls
     * @return  void
     */
    public function purge(array $urls) {

        foreach ($urls as $url) {
            $asynccache = Mage::getModel('aoeasynccache/asynccache');
            $asynccache->setTstamp(time());
            $asynccache->setMode('purgeVarnishUrl');
            $asynccache->setTags($url);
            $asynccache->setStatus('pending');
            $asynccache->save();
        }
    }

    /**
     * Use original purge method to finally purge all stored varnish urls
     *
     * @param   array $urls
     * @return  array with all errors
     */
    public function purgeVarnishUrls(array $urls) {

        return parent::purge($urls);
    }
}
