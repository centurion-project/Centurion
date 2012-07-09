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
 * @package     Centurion_File
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_File
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Nicolas Duteil <nd@octaveoctave.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_File_System
{
    /**
     * Remove recursively the directory named by dirname.
     *
     * @param string $dirname       Path to the directory
     * @param boolean $followLinks  Removes symbolic links if set to true
     * @return boolean              True if success otherwise false
     * @throws Exception            When the directory does not exist or permission denied
     */
    public static function rmdir($dirname, $followLinks = false)
    {
        if (!is_dir($dirname) && !is_link($dirname)) {
            throw new Exception(sprintf('Directory %s does not exist', $dirname));
        }

        if (!is_writable($dirname)) {
            throw new Exception('You do not have renaming permissions');
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        while ($iterator->valid()) {
            if (!$iterator->isDot()) {
                if (!$iterator->isWritable()) {
                    throw new Exception(sprintf('Permission denied for %s', $iterator->getPathName()));
                }

                if ($iterator->isLink() && false === (bool) $followLinks) {
                    $iterator->next();
                    continue;
                }

                if ($iterator->isFile()) {
                    unlink($iterator->getPathName());
                } else if ($iterator->isDir()) {
                    rmdir($iterator->getPathName());
                }
            }

            $iterator->next();
        }

        unset($iterator);

        return rmdir($dirname);
    }
}
