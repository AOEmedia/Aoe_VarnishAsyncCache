<?php

class Aoe_VarnishAsyncCache_Model_Cleaner extends Aoe_AsyncCache_Model_Cleaner {

	/**
	 * Process the queue
	 *
	 * @return true
	 */
	public function processQueue() {

        $jobs       = null;
		$collection = $this->getUnprocessedEntriesCollection();

		if (count($collection) > 0) {
			$jobs = $collection->extractJobs();

			foreach ($jobs as &$job) {
                $startTime = time();

                // process default entries like in original extension
                if ($job['mode'] != 'purgeVarnishUrl') {
                    Mage::app()->getCache()->clean($job['mode'], $job['tags'], true);

                }
                // purge varnish urls
                else {
                    $errors = Mage::helper('varnishasynccache')->purgeVarnishUrls($job['tags']);
                    if (!empty($errors)) {
                        foreach ($errors as $error) {
                            Mage::log($error);
                        }
                    }
                }

                $job['duration'] = time() - $startTime;
                Mage::log('[ASYNCCACHE] MODE: ' . $job['mode'] . ', DURATION: ' . $job['duration'] . ' sec, TAGS: ' . implode(', ', $job['tags']));
			}

			// delete all affected asynccache database rows
			foreach ($collection as $asynccache) { /* @var $asynccache Aoe_AsyncCache_Model_Asynccache */
				$asynccache->delete();
			}

		}

		// disabling asynccache (clear cache requests will be processed right away) for all following requests in this script call
		Mage::register('disableasynccache', true, true);

		return true;
	}
}
