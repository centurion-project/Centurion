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
 * @package     Centurion_Controller
 * @subpackage  Router
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Controller
 * @subpackage  Router
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Controller_Router_Route extends Zend_Controller_Router_Route
{
    protected $_translatedMessage = null;

    /**
     * Instantiates route based on passed Zend_Config structure
     *
     * @param Zend_Config $config Configuration object
     */
    public static function getInstance(Zend_Config $config)
    {
        $reqs = ($config->reqs instanceof Zend_Config) ? $config->reqs->toArray() : array();
        $defs = ($config->defaults instanceof Zend_Config) ? $config->defaults->toArray() : array();

        return new self($config->route, $defs, $reqs);
    }

    public function isTranslated()
    {
        return $this->_isTranslated;
    }

    public function __construct($route, $defaults = array(), $reqs = array(), Zend_Translate $translator = null, $locale = null)
    {
        $route               = trim($route, $this->_urlDelimiter);
        $this->_defaults     = (array) $defaults;
        $this->_requirements = (array) $reqs;
        $this->_translator   = $translator;
        $this->_locale       = $locale;

        if ($route !== '') {
            foreach (explode($this->_urlDelimiter, $route) as $pos => $part) {
                if (substr($part, 0, 1) == $this->_urlVariable && substr($part, 1, 1) != $this->_urlVariable) {
                    $name = substr($part, 1);

                    if (substr($name, 0, 1) === '@' && substr($name, 1, 1) !== '@') {
                        $name                  = substr($name, 1);
                        $this->_translatable[] = $name;
                        $this->_isTranslated   = true;
                    }

                    $this->_parts[$pos]     = (isset($reqs[$name]) ? $reqs[$name] : $this->_defaultRegex);
                    $this->_variables[$pos] = $name;
                } else {
                    if (substr($part, 0, 1) == $this->_urlVariable) {
                        $part = substr($part, 1);
                    }

                    if (substr($part, 0, 1) === '@' && substr($part, 1, 1) !== '@') {
                        $this->_isTranslated = true;
                    }

                    $this->_parts[$pos] = $part;

                    if ($part !== '*') {
                        $this->_staticCount++;
                    }
                }
            }
        }
    }

    /**
     * Set requirements
     *
     * @param  array $requirements
     * @return void
     */
    public function setRequirements($requirements)
    {
        $this->_requirements = $requirements;
    }

    /**
     * Matches a user submitted path with parts defined by a map. Assigns and
     * returns an array of variables on a successful match.
     *
     * @param string $path Path used to match against this routing map
     * @return array|false An array of assigned values or a false on a mismatch
     */
    public function match($path, $partial = false)
    {
        $local = $this->getTranslator()->getLocale();
        $translator = $this->getTranslator();
        
        if ($this->_isTranslated) {
            if ($this->_translatedMessage == null) {
                $this->_translatedMessage = array();
                
                foreach ($this->_parts as $part) {
                    if (substr($part, 0, 1) === '@' && substr($part, 1, 1) !== '@') {
                        $partSlugify = substr($part, 1);

                        $localeList = $this->getTranslator()->getList();
                        if (is_array($localeList)) {
                            foreach ($localeList as $locale) {
                                $tradSlugify = Centurion_Inflector::slugify($translator->translate($partSlugify, $locale));
                                if (!isset($this->_translatedMessage[$tradSlugify]))
                                    $this->_translatedMessage[$tradSlugify] = array();

                                $this->_translatedMessage[$tradSlugify][] = $partSlugify;
                            }
                        }
                    }
                }
            }
        }
        
        $pathStaticCount = 0;
        $values          = array();
        $matchedPath     = '';

        if (!$partial) {
            $path = trim($path, $this->_urlDelimiter);
        }

        if ($path !== '') {
            $path = explode($this->_urlDelimiter, $path);

            foreach ($path as $pos => $pathPart) {
                // Path is longer than a route, it's not a match
                if (!array_key_exists($pos, $this->_parts)) {
                    if ($partial) {
                        break;
                    } else {
                        return false;
                    }
                }

                $matchedPath .= $pathPart . $this->_urlDelimiter;

                // If it's a wildcard, get the rest of URL as wildcard data and stop matching
                if ($this->_parts[$pos] == '*') {
                    $count = count($path);
                    for($i = $pos; $i < $count; $i+=2) {
                        $var = urldecode($path[$i]);
                        if (!isset($this->_wildcardData[$var]) && !isset($this->_defaults[$var]) && !isset($values[$var])) {
                            $this->_wildcardData[$var] = (isset($path[$i+1])) ? urldecode($path[$i+1]) : null;
                        }
                    }

                    $matchedPath = implode($this->_urlDelimiter, $path);
                    break;
                }

                $name     = isset($this->_variables[$pos]) ? $this->_variables[$pos] : null;
                $pathPart = urldecode($pathPart);

                // Translate value if required
                $part = $this->_parts[$pos];
                if ($this->_isTranslated && (substr($part, 0, 1) === '@' && substr($part, 1, 1) !== '@'
                    && $name === null) || $name !== null && in_array($name, $this->_translatable)
                ) {
                    if (substr($part, 0, 1) === '@') {
                        $part = substr($part, 1);
                    }

                    if (isset($this->_translatedMessage[$pathPart])) {
                        foreach($this->_translatedMessage[$pathPart] as $translation) {
                            if ($translation === $part) {
                                $pathPart = $part;
                                break;
                            }
                        }
                    }

                }


                if (substr($part, 0, 2) === '@@') {
                    $part = substr($part, 1);
                }

                // If it's a static part, match directly
                if ($name === null && $part != $pathPart) {
                    return false;
                }

                // If it's a variable with requirement, match a regex. If not - everything matches
                if ($part !== null && !preg_match($this->_regexDelimiter . '^' . $part . '$' . $this->_regexDelimiter . 'iu', $pathPart)) {
                    return false;
                }

                // If it's a variable store it's value for later
                if ($name !== null) {
                    $values[$name] = $pathPart;
                } else {
                    $pathStaticCount++;
                }
            }
        }

        $defaults = $this->_defaults;

        foreach ($defaults as $part => $val) {
            if (substr($part, 0, 1) === '@' && substr($part, 1, 1) !== '@') {
                $part = substr($part, 1);
                $translatedPart = Centurion_Inflector::slugify($this->getTranslator()->translate($part));
                $defaults[$part] = $defaults['@'.$part];
                unset($defaults['@'.$part]);

                if ($translatedPart!== $part) {
                    if (isset($this->_wildcardData[$translatedPart])) {
                        $this->_wildcardData[$part] = $this->_wildcardData[$translatedPart];
                        unset($this->_wildcardData[$translatedPart]);
                    }
                }
            }
        }

        // Check if all static mappings have been matched
        if ($this->_staticCount != $pathStaticCount) {
            return false;
        }

        $return = $values + $this->_wildcardData + $defaults;

        // Check if all map variables have been initialized
        foreach ($this->_variables as $var) {
            if (!array_key_exists($var, $return)) {
                return false;
            }
        }

        $this->setMatchedPath(rtrim($matchedPath, $this->_urlDelimiter));

        $this->_values = $values;

        return $return;
    }

    /**
     * Assembles user submitted parameters forming a URL path defined by this route
     *
     * @param  array $data An array of variable and value pairs used as parameters
     * @param  boolean $reset Whether or not to set route defaults with those provided in $data
     * @return string Route path with user submitted parameters
     */
    public function assemble($data = array(), $reset = false, $encode = false, $partial = false)
    {
        if ($this->_isTranslated) {
            $translator = $this->getTranslator();

            if (isset($data['@locale'])) {
                $locale = $data['@locale'];
                unset($data['@locale']);
            } else {
                $locale = $this->getLocale();
            }
        }

        $url  = array();
        $flag = false;

        foreach ($this->_parts as $key => $part) {
            $name = isset($this->_variables[$key]) ? $this->_variables[$key]:null;

            $useDefault = false;
            if (isset($name) && array_key_exists($name, $data) && $data[$name] === null) {
                $useDefault = true;
            }

            if (isset($name)) {
                if (isset($data[$name]) && !$useDefault) {
                    $value = $data[$name];
                    unset($data[$name]);
                } elseif (!$reset && !$useDefault && isset($this->_values[$name])) {
                    $value = $this->_values[$name];
                } elseif (!$reset && !$useDefault && isset($this->_wildcardData[$name])) {
                    $value = $this->_wildcardData[$name];
                } elseif (isset($this->_defaults[$name])) {
                    $value = $this->_defaults[$name];
                } else {
                    require_once 'Zend/Controller/Router/Exception.php';
                    throw new Zend_Controller_Router_Exception($name . ' is not specified');
                }

                if ($this->_isTranslated && in_array($name, $this->_translatable)) {
                    $url[$key] = Centurion_Inflector::slugify($translator->translate($value, $locale));
                } else {
                    $url[$key] = $value;
                }
            } elseif ($part != '*') {
                if ($this->_isTranslated && substr($part, 0, 1) === '@') {
                    if (substr($part, 1, 1) !== '@') {
                        $url[$key] = Centurion_Inflector::slugify($translator->translate(substr($part, 1), $locale));
                    } else {
                        $url[$key] = substr($part, 1);
                    }
                } else {
                    if (substr($part, 0, 2) === '@@') {
                        $part = substr($part, 1);
                    }

                    $url[$key] = $part;
                }
            } else {
                $defaults = $this->getDefaults();

                foreach ($this->_wildcardData as $key => $val) {
                    if (substr($key, 0, 1) !== '@' && isset($defaults['@' . $key])) {
                        $this->_wildcardData['@' . $key] = $val;
                        unset($this->_wildcardData[$key]);
                    }
                }

                if (!$reset)
                    $data += $data + $this->_wildcardData;

                $dataTemp = array();

                foreach ($defaults as $key => $val) {
                    if (isset($data[$key])) {
                        $dataTemp[$key] = $data[$key];
                        unset($data[$key]);
                    }
                }

                $data = $dataTemp + $data;

                foreach ($data as $var => $value) {
                    if ($value !== null && (!isset($defaults[$var]) || $value != $defaults[$var])) {
                        if ($this->_isTranslated && substr($var, 0, 1) === '@') {
                            $url[$key++] = Centurion_Inflector::slugify($translator->translate(substr($var, 1), $locale));
                        } else {
                            if (isset($defaults['@'.$var])) {
                                $data['@'.$var] = $value;
                                unset($data[$var]);
                                continue;
                            }

                            $url[$key++] = $var;
                        }
                        $url[$key++] = $value;
                        $flag = true;
                    }
                }
            }
        }

        $return = '';

        foreach (array_reverse($url, true) as $key => $value) {
            $defaultValue = null;

            if (isset($this->_variables[$key])) {
                $defaultValue = $this->getDefault($this->_variables[$key]);

                if ($this->_isTranslated && $defaultValue !== null && isset($this->_translatable[$this->_variables[$key]])) {
                    $defaultValue = Centurion_Inflector::slugify($translator->translate($defaultValue, $locale));
                }
            }

            if ($flag || $value !== $defaultValue || $partial) {
                if ($encode) $value = rawurlencode($value);
                $return = $this->_urlDelimiter . $value . $return;
                $flag = true;
            }
        }

        return trim($return, $this->_urlDelimiter);
    }
}
