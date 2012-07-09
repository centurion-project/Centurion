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
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_View
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_View_Helper_Extend extends Zend_View_Helper_Abstract
{
    protected $_parent;

    protected $_parentVars = array();

    protected $_currentSection;

    protected $_ancestors = array();

    protected $_nestLevel;

    protected $_sectionDefaults = array();

    protected $_isDefining = false;

    /**
     * Extends a master view script. Registers a filter so that the render()
     * method is automatically called if omitted/forgotten.
     *
     * @param string $parent         FULL FILENAME of master view script
     * @param string $defaultSection Name of auto-opened section. Pass false to
     *                                disable auto-open.
     * @return $this
     */
    public function extend($parent = null, $defaultSection = 'content')
    {
        if (null !== $parent) {
            if (in_array($parent, $this->_ancestors)) {
                throw new Zend_View_Exception('Extend: circular script inheritance detected.');
            }

            if (null === $this->_nestLevel) {
                $this->view->addFilter('extend');
                $this->_nestLevel = 0;
            }

            $this->_parent = $parent;
            $this->_ancestors[] = $parent;
            $this->_nestLevel++;

            if (false !== $defaultSection) {
                $this->section($defaultSection);
            }
        }

        return $this;
    }

    /**
     * Returns true if a master view needs to be rendered.
     *
     * @return bool
     */
    public function isOpen()
    {
        return $this->_nestLevel > 0;
    }

    /**
     * Declare a section.
     *
     * All output from this point until end() is called will be captured and
     * assigned to a master view variable (e,g. the 'foo' section will become
     * $this->foo in the master view).
     *
     * Nested sections are disallowed, so the last open section will be auto-
     * closed when this is called.
     *
     * @param string $key Section name
     * @return $this
     */
    public function section($key)
    {
        $this->end();
        $this->_currentSection = $key;
        ob_start();

        return $this;
    }

    /**
     * Define the default content of a section.
     *
     * @param string $key Section name
     * @return $this
     */
    public function define($key = null)
    {
        if (null !== $key) {
            $this->section($key);
        }

        $this->_isDefining = true;

        return $this;
    }

    /**
     * Close a section.
     *
     * @return $this
     */
    public function end()
    {
        if (null !== $this->_currentSection) {
            $content = ob_get_clean();
            if (!$this->_isDefining) {
                $this->setCurrentSection($content);
            } else {
                $this->setSectionDefault($this->_currentSection, $content);
            }
        }

        return $this;
    }

    /**
     * Return the default section content of the master view. Only makes sense
     * when defining the default section content.
     *
     * Actually, that's impossible since the master view is evaluated last, so
     * some shenanigans are happening here.
     *
     * @return $this
     */
    public function super()
    {
        $content = ob_get_clean();
        $key = $this->_currentSection;

        if (!array_key_exists($key, $this->_parentVars)) {
            $this->_parentVars[$key] = array();
        } else if (!is_array($this->_parentVars[$key])) {
            $this->_parentVars[$key] = (array) $this->_parentVars[$key];
        }

        $this->_parentVars[$this->_currentSection][] = $content;
        ob_start();

        return $this;
    }

    /**
     * Sets the default content of a section.
     *
     * @param string $key     Section name
     * @param string $content The content
     * @return $this
     */
    public function setSectionDefault($key, $content)
    {
        $this->_sectionDefaults[$key] = $content;
        $this->view->$key = $this->evaluateSection($key);
        $this->_currentSection = null;
        $this->_isDefining = false;

        return $this;
    }

    /**
     * Returns the default section content or the view variable if it is defined
     *
     * @param string $key Section name
     * @return string
     */
    public function evaluateSection($key)
    {
        if (isset($this->view->$key)) {
            if (is_array($this->view->$key)) {
                return implode($this->getSectionDefault($key), $this->view->$key);
            } else {
                return $this->view->$key;
            }
        } else {
            return $this->getSectionDefault($key);
        }
    }

    /**
     * Returns the defined default section content.
     *
     * @param string $key     Section name
     * @param string $default Value to return if section has no default
     * @return string
     */
    public function getSectionDefault($key, $default = null)
    {
        return array_key_exists($key, $this->_sectionDefaults)
            ? $this->_sectionDefaults[$key]
            : $default
        ;
    }

    /**
     * Sets the content of the currently open section.
     *
     * @param string $content The content
     * @return $this
     */
    public function setCurrentSection($content)
    {
        $this->setSection($this->_currentSection, $content);
        $this->_currentSection = null;

        return $this;
    }

    /**
     * Sets the content of a section.
     *
     * @param string $key     Section name
     * @param string $content The content
     * @return $this
     */
    public function setSection($key, $content)
    {
        if (array_key_exists($key, $this->_parentVars) && is_array($this->_parentVars[$key])) {
            $this->_parentVars[$key][] = $content;
        } else {
            $this->_parentVars[$key] = $content;
        }

        return $this;
    }

    /**
     * Proxy to setSection()
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->setSection($key, $value);
    }

    /**
     * Proxy to evaluate, echoes the return value at the same time.
     *
     * Meant to be used inside define blocks, Echo'ed to make the mechanics
     * the same as super().
     *
     * @param string $key
     * @return string
     */
    public function __get($key)
    {
        $ret = $this->evaluateSection($key);

        echo $ret;

        return $ret;
    }

    /**
     * Replaces the view variables and renders the master view script.
     *
     * @return string
     */
    public function render()
    {
        if (null === $this->_parent) {
            return;
        }

        $this->_nestLevel--;
        $this->end();
        $this->view->assign($this->_parentVars);
        $output = $this->view->render($this->_parent);
        
        array_pop($this->_ancestors);

        return $output;
    }
}
