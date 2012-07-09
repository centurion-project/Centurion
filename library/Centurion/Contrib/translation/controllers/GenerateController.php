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
 * @todo        Implement generation for different adapter: array, po, etc.
 */
class Translation_GenerateController extends Centurion_Controller_Action
{
    public function init()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();

        parent::init();
    }

    public function indexAction()
    {
        Translation_Model_Manager::generate();

        Centurion_Signal::factory('clean_cache')->send($this);
        Centurion_Loader_PluginLoader::cleanCache(Centurion_Loader_PluginLoader::getIncludeFileCache());
        Centurion_Loader_PluginLoader::setStaticCachePlugin(null);
    }
}
