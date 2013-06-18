<?php

class Aoe_VarnishAsyncCache_Model_Observer extends Magneto_Varnish_Model_Observer
{
    /**
     * Temporary storage for already processed entries
     *
     * @var array
     */
    public $TAGS_ALREADY_PROCESSED = array();

    /**
     * Listens to application_clean_cache event and gets notified when a product/category/cms model is saved
     *
     * @param $observer Mage_Core_Model_Observer
     * @return Magneto_Varnish_Model_Observer
     */
    public function purgeCache($observer)
    {
        // if Varnish is not enabled on admin don't do anything
        if (!Mage::app()->useCache('varnish')) {
            return $this;
        }

        /** @var Aoe_VarnishAsyncCache_Helper_Data $helper */
        $helper = Mage::helper('varnishasynccache');
        /** @var Mage_Adminhtml_Model_Session $session */
        $session = Mage::getSingleton('adminhtml/session');
        $tags = $observer->getTags();

        // check if we should process tags from product which has no relevant changes
        $skippableProductIds = Mage::registry('skippableProductsForPurging');
        if (null !== $skippableProductIds) {
            foreach ((array) $tags as $tag) {
                if (preg_match('/^catalog_product_(\d+)?/', $tag, $match)) {
                    if (isset($match[1]) && in_array($match[1], $skippableProductIds)) {
                        return $this;
                    }
                }
            }
        }

        $urls = array();
        if ($tags == array()) {
            $errors = Mage::helper('varnish')->purgeAll();
            if (!empty($errors)) {
                $session->addError($helper->__("Varnish Purge failed"));
            } else {
                $session->addSuccess($helper->__("The Varnish cache storage has been flushed."));
            }

            return $this;
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
                    // get urls for product
                    $product = Mage::getModel('catalog/product')->load($tag_fields[2]);
                    $urls = array_merge($urls, $this->_getUrlsForProduct($product));
                } elseif ($tag_fields[1]=='category') {
                    $category = Mage::getModel('catalog/category')->load($tag_fields[2]);
                    $category_urls = $this->_getUrlsForCategory($category);
                    $urls = array_merge($urls, $category_urls);
                } elseif ($tag_fields[1]=='page') {
                    $urls = $this->_getUrlsForCmsPage($tag_fields[2]);
                }
            }
        }

        // transform urls to relative urls
        $relativeUrls = array();
        foreach ($urls as $url) {
            $relativeUrls[] = parse_url($url, PHP_URL_PATH);
        }

        if (!empty($relativeUrls)) {
            $errors = Mage::helper('varnish')->purge($relativeUrls);
            if (!empty($errors)) {
                $session->addError($helper->__("Some Varnish purges failed: <br/>") . implode("<br/>", $errors));
            } else {
                $count = count($relativeUrls);
                if ($count > 5) {
                    $relativeUrls = array_slice($relativeUrls, 0, 5);
                    $relativeUrls[] = '...';
                    $relativeUrls[] = $helper->__("(Total number of purged urls: %d)", $count);
                }
                $session->addSuccess(
                    $helper->__("Purges have been submitted successfully:<br/>") . implode("<br />", $relativeUrls)
                );
            }
        }

        return $this;
    }
}
