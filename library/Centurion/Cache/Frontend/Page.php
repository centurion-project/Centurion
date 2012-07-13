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
 * @package     Centurion_Cache
 * @subpackage  Frontend
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Cache
 * @subpackage  Frontend
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Cache_Frontend_Page extends Zend_Cache_Frontend_Page
{
    protected $_specificOptions = array(
        'http_conditional' => false,
        'debug_header' => false,
        'debug_footer' => true,
        'content_type_memorization' => false,
        'memorize_headers' => array(),
        'default_options' => array(
            'cache_with_get_variables' => false,
            'cache_with_post_variables' => false,
            'cache_with_session_variables' => false,
            'cache_with_files_variables' => false,
            'cache_with_cookie_variables' => false,
            'make_id_with_get_variables' => true,
            'make_id_with_post_variables' => true,
            'make_id_with_session_variables' => true,
            'make_id_with_files_variables' => true,
            'make_id_with_cookie_variables' => true,
            'cache' => true,
            'specific_lifetime' => false,
            'tags' => array(),
            'priority' => null,
            'prefixId' => ''
        ),
        'regexps' => array()
    );
    
    public function setDefaultPrefixId($id)
    {
        $this->_specificOptions['default_options']['prefixId'] = $id;
    }
    
    /**
     * Add a tag to the current cache (if have one)
     *
     * @param string|Centurion_Db_Table_Abstract|Centurion_Db_Table_Row_Abstract $tag
     * @return $this
     */
    public function addTag($tag)
    {
        Centurion_Cache_TagManager::addTag($tag);

        return $this;
    }

    /**
     * Callback for output buffering
     * (shouldn't really be called manually)
     *
     * @param  string $data Buffered output
     * @return string Data to send to browser
     */
    public function _flush($data)
    {
        $this->_activeOptions['tags'] = Centurion_Cache_TagManager::end($this->_activeOptions['tags']);

        return parent::_flush($data);
    }

    /**
     * Specific setter for the 'regexps' option (with some additional tests)
     *
     * @param  array $options Associative array
     * @return void
     */
    public function setRegexps($regexps)
    {
        return $this->_setRegexps($regexps);
    }
    
    /**
     * Make an id depending on REQUEST_URI and superglobal arrays (depending on options)
     *
     * @return mixed|false a cache id (string), false if the cache should have not to be used
     */
    protected function _makeId()
    {
        $id = parent::_makeId();
        
        if (false !== $id) {
            if (isset($this->_activeOptions['prefixId'])) {
                $id = $this->_activeOptions['prefixId'] . $id;
            }
        }
        
        return $id;
    }
    
    /**
     * Start the cache
     *
     * @param  string  $id       (optional) A cache id (if you set a value here, maybe you have to use Output frontend instead)
     * @param  boolean $doNotDie For unit testing only !
     * @return boolean True if the cache is hit (false else)
     */
    public function start($id = false, $doNotDie = false)
    {
        $this->_cancel = false;
        $lastMatchingRegexp = null;
        foreach ($this->_specificOptions['regexps'] as $regexp => $conf) {
            if (preg_match("`$regexp`", $_SERVER['REQUEST_URI'])) {
                $lastMatchingRegexp = $regexp;
            }
        }
        $this->_activeOptions = $this->_specificOptions['default_options'];
        if ($lastMatchingRegexp !== null) {
            $conf = $this->_specificOptions['regexps'][$lastMatchingRegexp];
            foreach ($conf as $key=>$value) {
                $this->_activeOptions[$key] = $value;
            }
        }
        if (!($this->_activeOptions['cache'])) {
            return false;
        }
        if (!$id) {
            $id = $this->_makeId();
            if (!$id) {
                return false;
            }
        }
        $array = $this->load($id);
        if ($array !== false) {
            $data = $array['data'];
            $headers = $array['headers'];
            $noDebug = false;
            if (!headers_sent()) {
                foreach ($headers as $key=>$headerCouple) {
                    $name = $headerCouple[0];
                    $value = $headerCouple[1];
                    if ($name == 'Content-Type') {
                        $noDebug = true;
                    }
                    header("$name: $value");
                }
            }
            
            if (!$noDebug && $this->_specificOptions['debug_header']) {
                echo 'DEBUG HEADER : This is a cached page !';
            }
            echo $data;
            
            if (!$noDebug && $this->_specificOptions['debug_footer']) {
                $metadata = $this->getMetadatas($id);
                
                $dateCreated = new Zend_Date($metadata['mtime']);
                $dateExpire = new Zend_Date($metadata['expire']);
                echo '<!-- Page in cache since: ' . $dateCreated->toString(Zend_Date::DATETIME_LONG) . ' -->' . "\n";
                echo '<!-- Will expire at: ' . $dateExpire->toString(Zend_Date::DATETIME_LONG) . ' -->' . "\n";
                //echo '<!-- With tag: ' . print_r($metadata['tags'], true) . ' -->';
            }
            if ($doNotDie) {
                return true;
            }
            die();
        }
        ob_start(array($this, '_flush'));
        ob_implicit_flush(false);
        
        if (true === $this->_activeOptions['cache'] && (false !== $id || $this->_makeId())) {
            Centurion_Cache_TagManager::start((false !== $id) ? $id : $this->_makeId(), $this, false);
        }

        return false;
    }
}
