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
 * @package     Centurion_Iterator
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Iterator
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Iterator_Directory extends DirectoryIterator
{
    /**
     * Return the extension of current file
     * @return string
     */
    public function getExtension()
    {
        return substr($this->getFilename(),
                      strrpos($this->getFilename(), '.') + 1);
    }

    /**
     * Return the filename without extension of the current file
     * @return string
     */
    public function getFilenameWithoutExtension()
    {
        return substr($this->getFilename(),
                      0, strrpos($this->getFilename(), '.'));
    }
}