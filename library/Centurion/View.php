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
 * @package     Centurion_View
 * @copyright   Copyright (c) 2008-2009 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_View
 * @copyright   Copyright (c) 2008-2009 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_View extends Zend_View
{
    protected static $_defaultCache = null;

    protected $_loaderTypes = array('filter', 'helper');

    protected $_loaders = array();

    protected $_isPartial = false;

    public static function setDefaultCache($cache = null)
    {
        self::$_defaultCache = self::_setupDefaultCache($cache);
    }

    protected static function _setupDefaultCache($cache)
    {
        if (null === $cache) {
            return null;
        }

        if (is_string($cache)) {
            $cache = Zend_Registry::get($cache);
        }

        if (!$cache instanceof Zend_Cache_Core) {
            throw new Centurion_Exception('Argument must be of type Zend_Cache_Core, or a Registry key where a Zend_Cache_Core object is stored');
        }

        return $cache;
    }

    public function getCache()
    {
        return self::$_defaultCache;
    }

    public function render($name)
    {
        if (is_array($name)) {
            $name = $this->_selectScript($name);
        }

        // loss of performance : $this->_selectScript uses _script and render uses it too.
        return parent::render($name);
    }
    
	/**
     * Processes a view script and returns the output.
     *
     * @param string $name The script name to process.
     * @return string The script output.
     */
    public function renderToString($viewScript, $kwargs)
    {
        foreach ($kwargs as $key => $value) {
            $this->assign($key, $value);
        }

        return $this->render($viewScript);
    }

    public function selectScript($scripts)
    {
        return $this->_selectScript($scripts);
    }
    
    /**
     * 
     * @param array $scripts
     * @throws Zend_View_Exception
     * @return string first existing view script
     */
    protected function _selectScript(array $scripts)
    {
        foreach ($scripts as $script) {
            try {
                $this->_script($script);

                return $script;
            } catch (Zend_View_Exception $e) {
                continue;
            }
        }

        $message = "script '". implode(PATH_SEPARATOR, $scripts) ."' not found in path ("
                 . implode(PATH_SEPARATOR, $this->_path['script'])
                 . ")";

        throw new Zend_View_Exception($message, $this);
    }

    /**
     * Retrieve plugin loader for a specific plugin type
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader
     */
    public function getPluginLoader($type)
    {
        $type = strtolower($type);

        if (!in_array($type, $this->_loaderTypes)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception(sprintf('Invalid plugin loader type "%s"; cannot retrieve', $type));
            $e->setView($this);
            throw $e;
        }

        if (!array_key_exists($type, $this->_loaders)) {
            $prefix     = 'Zend_View_';
            $pathPrefix = 'Zend/View/';

            $pType = ucfirst($type);
            switch ($type) {
                case 'filter':
                case 'helper':
                default:
                    $prefix     .= $pType;
                    $pathPrefix .= $pType;
                    $loader = new Centurion_Loader_PluginLoader(array(
                        $prefix => $pathPrefix
                    ), $type);
                    $this->_loaders[$type] = $loader;
                    break;
            }
        }

        return $this->_loaders[$type];
    }
    
    /**
     * 
     * @param boolean $isPartial
     * @return $this
     */
    public function setIsPartial($isPartial)
    {
        $this->_isPartial = $isPartial;
        return $this;
    }

    /**
     * Return if current view is partial
     * @return boolean
     */
    public function isPartial()
    {
        return $this->_isPartial;
    }
}
