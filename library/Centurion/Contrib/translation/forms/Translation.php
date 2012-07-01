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
class Translation_Form_Translation extends Centurion_Form
{
    protected $_exclude = array();

    protected $_instance = null;

    protected $_languageRowSet = null;
    protected $_languageReferenceRow = null;

    public function hasInstance()
    {
        return ($this->_instance !== null);
    }

    public function __construct($options = array())
    {
        parent::__construct($options);

        $subForm = new Centurion_Form();

        $this->addSubForm($subForm, 'language');
    }

    public function setReference($languageRow)
    {
        $this->_languageReferenceRow = $languageRow;
    }

    public function setLanguageToTraduct($languageRowSet)
    {
        $subForm = $this->language;
        foreach ($languageRowSet as $languageRow) {
            $subForm->addElement('textarea', (string) $languageRow->id, array('label' => $languageRow->locale));
            
            $translationRowset = Centurion_Db::getSingleton('translation/translation')->select(true)->filter(array('uid_id' => $this->_instance->id, 'language_id' => $languageRow->id))->fetchAll();

            foreach($translationRowset as $translationRow) {
                if (isset($subForm->{$translationRow->language_id}))
                    $subForm->{$translationRow->language_id}->setValue($translationRow->translation);
            }
        }
    }

    public function save($adapter = null)
    {
        $translationTable = Centurion_Db::getSingleton('translation/translation');
        $id = $this->_instance->id;

        foreach ($this->language as $key => $val) {
            if (!$val->getValue()) {
                continue;
            }

            $translationTable->save(array('uid_id'      => $id,
                                          'language_id' => $key,
                                          'translation' => $val->getValue()));
        }

        return $this->_instance;
    }

    public function setInstance($instance)
    {
        $this->_instance = $instance;
        if (null !== $instance) {
            if ($this->_languageReferenceRow === null) {
                $name = 'Original';
            } else {
                $name = $this->_languageReferenceRow->name;
            }

            $translation = $instance->getTranslationFor($this->_languageReferenceRow);

            $this->addElement('textarea', 'original', array('disabled' => 'disabled', 'value' => $translation, 'label' => $this->_translate('Reference (%s)', $name)));
            $this->moveElement('original', Centurion_Form::FIRST);

            $this->setLegend($this->_translate('Translate'));

            //Unused : this is done in setLanguageToTraduct
//            $translationRowset = Centurion_Db::getSingleton('translation/translation')->all(array('uid_id = ?' => $instance->id));
//
//            foreach($translationRowset as $translationRow) {
//                if (isset($this->language->{$translationRow->language_id}))
//                    $this->language->{$translationRow->language_id}->setValue($translationRow->translation);
//            }
        }

        return $this;
    }
}
