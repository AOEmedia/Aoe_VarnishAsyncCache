<?php

class Aoe_VarnishAsyncCache_Model_Observer extends Magneto_Varnish_Model_Observer {

    /**
     * Temporary save of already
     * processed entries
     *
     * @var array
     */
    public $TAGS_ALREADY_PROCESSED = array();

    /**
     * Listens to application_clean_cache event and gets notified when a product/category/cms
     * model is saved.
     *
     * @param $observer Mage_Core_Model_Observer
     * @return Magneto_Varnish_Model_Observer
     */
    public function purgeCache($observer)
    {
        // If Varnish is not enabled on admin don't do anything
        if (!Mage::app()->useCache('varnish')) {
            return;
        }

        $tags = $observer->getTags();

        // check if we should process tags from product which has no relevant changes
        $skippableProductIds = Mage::registry('skippableProductsForPurging');
        if (null !== $skippableProductIds) {
            foreach ((array) $tags as $tag) {
                if (preg_match('/^catalog_product_(\d+)?/', $tag, $match)) {
                    if (isset($match[1]) && in_array($match[1], $skippableProductIds)) {
                        return;
                    }
                }
            }
        }

        $urls = array();

        if ($tags == array()) {
            $errors = Mage::helper('varnish')->purgeAll();
            if (!empty($errors)) {
                Mage::getSingleton('adminhtml/session')->addError("Varnish Purge failed");
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess("The Varnish cache storage has been flushed.");
            }
            return;
        }

        // compute the urls for affected entities
        foreach ((array)$tags as $tag) {
            if (in_array($tag, $this->TAGS_ALREADY_PROCESSED)) {
                continue;
            }

            $this->TAGS_ALREADY_PROCESSED[] = $tag;

            //catalog_product_100 or catalog_category_186
            $tag_fields = explode('_', $tag);
            if (count($tag_fields)==3) {
                if ($tag_fields[1]=='product') {
                    // Mage::log("Purge urls for product " . $tag_fields[2]);

                    // get urls for product
                    $product = Mage::getModel('catalog/product')->load($tag_fields[2]);
                    $urls = array_merge($urls, $this->_getUrlsForProduct($product));
                } elseif ($tag_fields[1]=='category') {
                    // Mage::log('Purge urls for category ' . $tag_fields[2]);

                    $category = Mage::getModel('catalog/category')->load($tag_fields[2]);
                    $category_urls = $this->_getUrlsForCategory($category);
                    $urls = array_merge($urls, $category_urls);
                } elseif ($tag_fields[1]=='page') {
                    $urls = $this->_getUrlsForCmsPage($tag_fields[2]);
                }
            }
        }

        // Transform urls to relative urls
        $relativeUrls = array();
        foreach ($urls as $url) {
            $relativeUrls[] = parse_url($url, PHP_URL_PATH);
        }
        // Mage::log("Relative urls: " . var_export($relativeUrls, True));

        if (!empty($relativeUrls)) {
            $errors = Mage::helper('varnish')->purge($relativeUrls);
            if (!empty($errors)) {
                Mage::getSingleton('adminhtml/session')->addError(
                    "Some Varnish purges failed: <br/>" . implode("<br/>", $errors));
            } else {
                $count = count($relativeUrls);
                if ($count > 5) {
                    $relativeUrls = array_slice($relativeUrls, 0, 5);
                    $relativeUrls[] = '...';
                    $relativeUrls[] = "(Total number of purged urls: $count)";
                }
                Mage::getSingleton('adminhtml/session')->addSuccess("Purges have been submitted successfully:<br/>" . implode("<br />", $relativeUrls));            }
        }

        return $this;
    }
}
