<?php

class Aoe_VarnishAsyncCache_Model_AsyncCacheObserver {

	/**
	 * Post process job collection
	 *
	 * @param Mage_Core_Model_Observer $observer
	 */
	public function postProcessJobCollection(Varien_Event_Observer $observer) {
		$jobCollection = $observer->getData('jobCollection'); /* @var $jobCollection Aoe_AsyncCache_Model_JobCollection */

		if(!$jobCollection) {
			return;
		}
		foreach ($jobCollection as $job) { /* @var $job Aoe_AsyncCache_Model_Job */
			if (!$job->getIsProcessed() && $job->getMode() == Aoe_VarnishAsyncCache_Helper_Data::MODE_PURGEVARNISHURL) {

				$startTime = time();
				$errors = Mage::helper('varnishasynccache')->purgeVarnishUrls($job->getTags());
				$job->setDuration(time() - $startTime);
				$job->setIsProcessed(true);

				if (!empty($errors)) {
					foreach ($errors as $error) {
						Mage::log($error);
					}
				}

				Mage::log(sprintf('[ASYNCCACHE] MODE: %s, DURATION: %s sec, TAGS: %s',
					$job->getMode(),
					$job->getDuration(),
					implode(', ', $job->getTags())
				));

				$job->setIsProcessed(true);
			}
		}
	}

}