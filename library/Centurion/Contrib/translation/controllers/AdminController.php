<?php
/**
 * Centurion
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@centurion-project.org so we can send you a copy immediately.
 *
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Translation
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Translation
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Translation_AdminController extends Centurion_Controller_CRUD
{
    protected $_cacheTagName = array('translation');

    public function filterCaseInsensitive($value, &$sqlFilter)
    {
        $sqlFilter[] = new Zend_Db_Expr(Centurion_Db_Table_Abstract::getDefaultAdapter()->quoteInto('uid like ? COLLATE utf8_general_ci', '%' . $value . '%'));
    }

    public function putAction()
    {
        parent::putAction();

        Translation_Model_Manager::generate();

        $this->getInvokeArg('bootstrap')
             ->getResource('cachemanager')
             ->clean(Zend_Cache::CLEANING_MODE_ALL);
    }

    public function _postInitForm()
    {
        parent::_postInitForm();
        $this->_getParams();
        $this->_form->setReference($this->getLanguageReference());

        $select = Centurion_Db::getSingleton('translation/language')->select(true);
        if (null !== $this->_getParam('show', null)) {
            $select->filter(array('id__in' => $this->_getParam('show', null)));
        }
        $languageRows = $select->fetchAll();

        $this->_form->setLanguageToTraduct($languageRows);
    }

    public function postAction()
    {
        parent::postAction();

        Translation_Model_Manager::generate();

        $this->getInvokeArg('bootstrap')
             ->getResource('cachemanager')
             ->clean(Zend_Cache::CLEANING_MODE_ALL);
    }

    protected function _getModel()
    {
        if (null === $this->_model) {
            $this->_model = Centurion_Db::getSingleton('translation/uid');
        }

        return $this->_model;
    }

    public function getLanguageReference()
    {
        static $languageReference = false;

        if ($languageReference === false) {
            $filter = $this->_getParam('filter');
            if (isset($filter['reference'])) {
                $languageReference = Centurion_Db::getSingleton('translation/language')->findOneById($filter['reference']);
            }

            if ($languageReference === false) {
                $languageReference = Translation_Traits_Common::getDefaultLanguage();
            }
        }

        return $languageReference;
    }

    public function uid($row)
    {
        $languageReference = $this->getLanguageReference();
        $translation = $row->getTranslationFor($languageReference);

        if ($languageReference !== null) {
            $locale = $languageReference->locale;
            $flag = '<img src="'.$languageReference->flag.'" alt="'.$locale.'"/>';
        } else {
            $flag = '';
            $locale = 'Original';
        }

        return '<div class="text-wrapper">'.$flag.'<a href="' .$this->view->url(array('action' => 'get', 'id' => $row->id)). '" class="edit">' . $translation . '</a></div>';
    }

    public function translations($row)
    {
        static $languageRows = null;

        if (null === $languageRows) {
            $select = Centurion_Db::getSingleton('translation/language')->select(true);
            if (null !== $this->_getParam('show', null)) {
                $select->filter(array('id__in' => $this->_getParam('show', null)));
            }
            $languageRows = $select->fetchAll();
        }

        $str = '';

        foreach ($row->translations as $translation) {
            $translations[$translation->language__locale] = $translation->translation;
        }

        foreach ($languageRows as $language) {
            if (!isset($translations[$language->locale]))
                $translations[$language->locale] = $row->uid;
            $str .= '<div class="text-wrapper"><img src="' . $language->flag . '" alt="' . $language->locale . '"/>' . $translations[$language->locale] . '</div>';
        }
        return $str;
    }

    public function preDispatch()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();
        $this->_helper->layout->setLayout('admin');
        parent::preDispatch();
    }
    
    public function init()
    {
        $this->view->noAddButton = true;

        $this->_formClassName = 'Translation_Form_Translation';

        $this->_displays = array(
            'uid' => array(
                'label' => $this->view->translate('Reference text'),
                'type' => self::COLS_CALLBACK,
            ),
            'translations' => array(
               'label' => $this->view->translate('Translations'),
               'type' => self::COLS_CALLBACK,
                'sortable' => false,
            ),
        );

        $languageData = array();
        $languageName = array_flip(Zend_Locale_Data_Translation::$languageTranslation);

        foreach (Centurion_Db::getSingleton('translation/language')->fetchAll() as $language) {
            $languageData[$language->id] = $languageName[$language->locale];
        }

        $referenceData = array_merge(array(0 => 'Original'), $languageData);

        $tagData = array();

        foreach (Centurion_Db::getSingleton('translation/tag')->fetchAll() as $tag) {
            $tagData[$tag->id] = ucfirst($tag->tag);
        }

        $this->_filters = array(
                'uid' => array(
                    'behavior' => self::FILTER_BEHAVIOR_CALLBACK,
                    'label' => $this->view->translate('Id'),
                    'callback' => array($this, 'filterCaseInsensitive')
                    
                ),
                'reference' => array(
                        'behavior' => self::FILTER_BEHAVIOR_NOTHING,
                        'type' => self::FILTER_TYPE_RADIO,
                        'label' => $this->view->translate('Reference language'),
                        'data' => $referenceData,
                    ),
                'show' => array(
                        'behavior' => self::FILTER_BEHAVIOR_NOTHING,
                        'type' => self::FILTER_TYPE_CHECKBOX,
                        'label' => $this->view->translate('Language to translate'),
                        'data' => $languageData,
                        'column' => 'languages',
                    ),
                'tags__id' => array(
                        'behavior' => self::FILTER_BEHAVIOR_IN,
                        'type' => self::FILTER_TYPE_CHECKBOX,
                        'label' => $this->view->translate('Tags'),
                        'data' => $tagData,
                    ),
            );

        parent::init();

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Manage translation'));
    }
}
