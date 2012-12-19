<?php

class Aoe_VarnishAsyncCache_Helper_Data extends Magneto_Varnish_Helper_Data {

	/**
	 * Store an array of urls in cache for purging with Aoe_AsyncCache extension later
	 *
	 * @param   array   $urls
	 * @throws  Zend_Db_Statement_Exception
	 * @return  void
	 */
    public function purge(array $urls) {

        foreach ($urls as $url) { /* @var $url string */
            $asynccache = Mage::getModel('aoeasynccache/asynccache'); /* @var $asynccache Aoe_AsyncCache_Model_Asynccache */
            $asynccache->setTstamp(time());
            $asynccache->setMode('purgeVarnishUrl');
            $asynccache->setTags($url);
            $asynccache->setStatus('pending');

            try {
                $asynccache->save();
            } catch (Zend_Db_Statement_Exception $e) {
                if ($e->getCode() != 23000) { // Integrity constraint violation: 1062 Duplicate entry '...' for key 'IDX_ASYNCCACHE_MODE_TAGS_STATUS'
                    throw $e;
                }
                // otherwise ignore duplicate entry...
            }

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
