<?php

class Aoe_VarnishAsyncCache_Model_Mysql4_Asynccache_Collection extends Aoe_AsyncCache_Model_Mysql4_Asynccache_Collection {
    
    /**
     * Extract jobs
	 * Combines job to reduce cache operations
     * 
     * @return array
     */
    public function extractJobs() {

    	$jobs           = array();
    	$matchingAnyTag = array();

		foreach ($this as $asynccache) {
			$mode = $asynccache->getMode();
			$tags = $this->getTagArray($asynccache->getTags());

			if ($mode == 'all') {
				return array(array('mode' => 'all', 'tags' => array()));
			} elseif ($mode == 'matchingAnyTag') {
				$matchingAnyTag = array_merge($matchingAnyTag, $tags);
			} elseif (($mode == 'matchingTag') && (count($tags) <= 1)) {
				$matchingAnyTag = array_merge($matchingAnyTag, $tags);
			} else {
				$jobs = $this->addCustomJob($jobs, $mode, $tags);
			}
		}
		$matchingAnyTag = array_unique($matchingAnyTag);
		if (count($matchingAnyTag) > 0) {
			array_unshift($jobs, array('mode' => 'matchingAnyTag', 'tags' => $matchingAnyTag));
		}

		return $jobs;
    }
}
