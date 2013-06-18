<?php
/**
 * Class Aoe_VarnishAsyncCache_Adminhtml_Varnishasynccache_BlacklistUrlPatternController
 *
 * @author Dmytro Zavalkin <dmytro.zavalkin@aoemedia.de>
 */
class Aoe_VarnishAsyncCache_Adminhtml_Varnishasynccache_BlacklistUrlPatternController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init actions
     *
     * @return $this
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('system/varnishasynccache_blacklistUrlPattern')
            ->_addBreadcrumb(
                  Mage::helper('varnishasynccache')->__('System'),
                  Mage::helper('varnishasynccache')->__('System')
            )
            ->_addBreadcrumb(
                  Mage::helper('varnishasynccache')->__('Varnish Cache Url Blacklist'),
                  Mage::helper('varnishasynccache')->__('Varnish Cache Url Blacklist')
            );
        return $this;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_title(Mage::helper('varnishasynccache')->__('System'))
             ->_title(Mage::helper('varnishasynccache')->__('Varnish Cache Url Blacklist'));

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Create new blacklist url
     */
    public function newAction()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    /**
     * Edit blacklist url
     */
    public function editAction()
    {
        $this->_title(Mage::helper('varnishasynccache')->__('System'))
             ->_title(Mage::helper('varnishasynccache')->__('Varnish Cache Url Blacklist'));

        /** @var $model Aoe_VarnishAsyncCache_Model_BlacklistUrlPattern */
        $model = Mage::getModel('varnishasynccache/blacklistUrlPattern');

        $blacklistUrlPatternId = $this->getRequest()->getParam('id');
        if ($blacklistUrlPatternId) {
            $model->load($blacklistUrlPatternId);

            if (!$model->getId()) {
                $this->_getSession()->addError(
                    Mage::helper('varnishasynccache')->__('Blacklist url pattern does not exist.')
                );
                $this->_redirect('*/*/');
                return;
            }
            // prepare title
            $breadCrumb = Mage::helper('varnishasynccache')->__('Edit blacklist url pattern (ID: %d)', $model->getId());
        } else {
            $breadCrumb = Mage::helper('varnishasynccache')->__('New url pattern');
        }

        // Init breadcrumbs
        $this->_title($breadCrumb);
        $this->_initAction()->_addBreadcrumb($breadCrumb, $breadCrumb);

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        Mage::register('blacklist_url_pattern', $model);

        $this->renderLayout();
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        $redirectPath   = '*/*';
        $redirectParams = array();

        // check if data sent
        $data = $this->getRequest()->getPost();
        if ($data) {
            // init model and set data
            /** @var $model Aoe_VarnishAsyncCache_Model_BlacklistUrlPattern */
            $model = Mage::getModel('varnishasynccache/blacklistUrlPattern');

            // if blacklist url pattern exists, try to load it
            $blacklistUrlPatternId = $this->getRequest()->getParam('id');
            if ($blacklistUrlPatternId) {
                $model->load($blacklistUrlPatternId);
            }

            $model->addData($data);

            try {
                $hasError = false;

                // save the data
                $model->save();

                // display success message
                $this->_getSession()->addSuccess(
                    Mage::helper('varnishasynccache')->__('Blacklist url pattern has been saved.')
                );

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $redirectPath   = '*/*/edit';
                    $redirectParams = array('id' => $model->getId());
                }
            } catch (Mage_Core_Exception $e) {
                $hasError = true;
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $hasError = true;
                $this->_getSession()->addException($e,
                    Mage::helper('varnishasynccache')->__('An error occurred while saving blacklist url pattern.')
                );
            }

            if ($hasError) {
                $this->_getSession()->setFormData($data);
                $redirectPath   = '*/*/edit';
                $redirectParams = array('id' => $this->getRequest()->getParam('id'));
            }
        }

        $this->_redirect($redirectPath, $redirectParams);
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        // check if we know what should be deleted
        $blacklistUrlPatternId = $this->getRequest()->getParam('id');
        if ($blacklistUrlPatternId) {
            try {
                // init model and delete
                /** @var $model Aoe_VarnishAsyncCache_Model_BlacklistUrlPattern */
                $model = Mage::getModel('varnishasynccache/blacklistUrlPattern');
                $model->load($blacklistUrlPatternId);
                if (!$model->getId()) {
                    Mage::throwException(
                        Mage::helper('varnishasynccache')->__('Unable to find a blacklist url pattern.')
                    );
                }
                $model->delete();

                // display success message
                $this->_getSession()->addSuccess(
                    Mage::helper('varnishasynccache')->__('Blacklist url pattern has been deleted.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('varnishasynccache')->__('An error occurred while deleting blacklist url pattern.')
                );
            }
        }

        // go to grid
        $this->_redirect('*/*/');
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        /** @var Mage_Admin_Model_Session $session */
        $session = Mage::getSingleton('admin/session');
        switch ($this->getRequest()->getActionName()) {
            case 'new':
            case 'save':
                return $session->isAllowed('system/varnishasynccache_blacklistUrlPattern/save');
                break;
            case 'delete':
                return $session->isAllowed('system/varnishasynccache_blacklistUrlPattern/delete');
                break;
            default:
                return $session->isAllowed('system/varnishasynccache_blacklistUrlPattern');
                break;
        }
    }

    /**
     * Grid ajax action
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Mass delete action
     */
    public function massDeleteAction()
    {
        $patternIds = $this->getRequest()->getParam('pattern_ids');
        if (!is_array($patternIds)) {
            $this->_getSession()->addError(
                Mage::helper('varnishasynccache')->__('Please select pattern(s) to delete.')
            );
        } else {
            if (!empty($patternIds)) {
                try {
                    foreach ($patternIds as $patternId) {
                        Mage::getModel('varnishasynccache/blacklistUrlPattern')->setId($patternId)
                            ->delete();
                    }
                    $this->_getSession()->addSuccess(
                        Mage::helper('varnishasynccache')->__('Total of %d pattern(s) have been deleted.', count($patternIds))
                    );
                } catch (Mage_Core_Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                } catch (Exception $e) {
                    $this->_getSession()->addException($e,
                        Mage::helper('varnishasynccache')->__('An error occurred while deleting blacklist url pattern(s).')
                    );
                }
            }
        }
        $this->_redirectReferer();
    }
}
