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
class Translation_Model_Log_Translation extends Zend_Log
{
    protected $_testedLanguage = array();

    protected $_testedUid = array();

    public function notice($param)
    {
        if (!is_array($param))
            return;

        $inserted = false;

        if (!isset($this->_testedLanguage[$param[1]])) {
            $languageTable = Centurion_Db::getSingleton('translation/language');

            $languageRow = $languageTable->findOneByLocale($param[1]);
            if (null === $languageRow) {
                $inserted = true;
                $languageRow = $languageTable->insert(array('locale' => $param[1]));
            }

            $this->_testedLanguage[$param[1]] = true;
        }

        if (!isset($this->_testedUid[$param[0]]) && trim($param[0]) !== '') {
              $uidTable = Centurion_Db::getSingleton('translation/uid');
              list($uidRow, $inserted) = $uidTable->getOrCreate(array('uid' => $param[0]));
              
            /*if (null === $uidRow) {
                $inserted = true;
                $uidRow = $uidTable->insert(array('uid' => $param[0]));
            }*/

            if ($inserted)
                //TODO: send a signal to bootstrap for clearing cache
                Zend_Controller_Front::getInstance()->getParam('bootstrap')
                                                    ->getResource('cachemanager')
                                                    ->getCache('core')
                                                    ->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('translation'));
            $this->_testedUid[$param[0]] = true;
        }
    }
}
