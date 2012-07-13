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
 * @author      Mathias Desloges <m.desloges@gmail.com>
 */
class Centurion_View_Helper_SmartTextCrop
{
    public $textStyleTags = array('span', 'b', 'strong', 'em', 'font');

    protected $_openedTag = array();

    protected function _isTag($string)
    {
        $string = trim($string);

        return 0 !== preg_match('(<[^>]+>)', $string, $matches);
    }

    /**
     * @param $tag
     * @return $this
     */
    protected function _openTag($tag)
    {
        $tag = trim($tag);
        if (strlen($tag)) {

            $tag = $this->_extractTagName($tag);

            // the tag is an auto closing tag
            if ('/' == substr($tag, -1)) {
                return $this;
            }

            array_push($this->_openedTag, $tag);
        }

        return $this;
    }

    protected function _extractTagName($tagString)
    {
        // removes '<' and '>'
        $tag = trim($tagString,'<> ');

        $whiteSpacePos = strpos($tag, ' ');
        if ($whiteSpacePos !== false)
            $tag = substr($tag, 0, $whiteSpacePos);

        return $tag;
    }

    protected function _closeLastOpenendTag()
    {
        $tag = array_pop($this->_openedTag);
        if ($tag) {
            return sprintf('</%s>', $tag);
        }

        return '';
    }

    protected function _isClosingTag($tag)
    {
        // removes '<' and '>'
        $tag = trim($tag, '<>');

        // the tag is an auto closing tag
        return ('/' == substr($tag, 0, 1));
    }

    protected function _analysePart($part)
    {
        foreach (array(1, 3) as $partIndex) {
            if (isset($part[$partIndex]) && $this->_isTag($part[$partIndex])) {
                if ($this->_isClosingTag($part[$partIndex])) {
                    $this->_closeLastOpenendTag();
                } else {
                    $this->_openTag($part[$partIndex]);
                }
            }
        }
    }

    public function simpleTextCrop($text, $limit, $trailingString = '...')
    {
        $cleanText = html_entity_decode(strip_tags($text), null, 'UTF-8');

        $len = strlen($cleanText);

        if ($len > $limit) {
            return sprintf('%s%s', htmlentities(substr($cleanText, 0, $limit), null, 'UTF-8'), $trailingString);
        }

        return $cleanText;
    }

    /**
     * @param string $html
     * @param int $limit
     * @param bool $cropWord
     * @param bool $cropTag
     * @param array $tagWhilteList
     * @return $this|string
     */
    public function smartTextCrop($html = '', $limit = 0, $cropWord = false, $cropTag = true, $tagWhilteList = array())
    {
        if (0 == func_num_args()) {
            return $this;
        }

        // polymorphism
        if (is_array($cropWord)) {
            $tagWhilteList = $cropWord;
            $cropWord = false;
        }

        preg_match_all("/(<[^>]+>)?([^<^>]*)(<[^>]+>)?/", $html, $matches, PREG_SET_ORDER);

        $output = '';
        $count = 0;
        $outputCount = 0;

        $part = reset($matches);

        if ($part) {
            $count = strlen(html_entity_decode($part[2], null, 'UTF-8'));
            while ($count < $limit) {
                $output .= $part[0];
                $outputCount += strlen(html_entity_decode($part[2], null, 'UTF-8'));

                $this->_analysePart($part);

                $part = next($matches);

                if ($part) {
                    $count += strlen(html_entity_decode($part[2], null, 'UTF-8'));
                } else
                    break;
            }

            $counterpart = '';
            if ($part) {
                if (!$cropTag) {
                    $lastTag = end($this->_openedTag);
                    if ($lastTag) {
                        $output = substr($output, 0, strrpos($output, sprintf('<%s', $lastTag)));
                    }
                } else {
                    $counterpart = substr($part[2], 0, $limit - $outputCount);

                    if (!$cropWord) {
                        $counterpart = substr($counterpart, 0, strrpos($counterpart, " "));
                    }

                    $counterpart = sprintf('%s%s...%s', $part[1], $counterpart, isset($part[3]) ? $part[3]:'');

                    $this->_analysePart($part);
                }

                $openTag = end($this->_openedTag);
                while (count($this->_openedTag)) {
                    $tag = $this->_closeLastOpenendTag();
                    $counterpart .= $tag;
                    $openTag = prev($this->_openedTag);
                }
            }
        }

        $output = $output . $counterpart;

        if (count($tagWhilteList))
            return strip_tags($output, sprintf('<%s>', implode('><', (array) $tagWhilteList)));

        return $output;
    }
}
