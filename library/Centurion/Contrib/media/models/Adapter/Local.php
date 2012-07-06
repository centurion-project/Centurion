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
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Media_Model_Adapter_Local extends Media_Model_Adapter_Abstract
{
    public function getUrl($dest)
    {
        return $this->getOption('url') . $dest;
    }

    public function read($dest)
    {
        $dest = $this->getOption('path') . $dest;

        return file_get_contents($dest);
    }

    public function delete($dest)
    {
        $dest = $this->getOption('path') . $dest;

        if (file_exists($dest))
            return unlink($dest);
        return false;
    }

    public function update($source, $dest)
    {
        $dest = $this->getOption('path') . $dest;
        unlink($dest);

        return rename($source, $dest);
    }

    public function _mkdir($path)
    {
        $dest = $this->getOption('path');

        $partsArray = preg_split('`([/]|[\\\\])`', $path);

        foreach ($partsArray as $part) {
            $dest .= $part . DIRECTORY_SEPARATOR;
            if (!is_dir($dest)) {
                @mkdir($dest, 0777);
                @chmod($dest, 0777); // see #ZF-320 (this line is required in some configurations)
            }
        }
        return true;
    }

    public function save($source, $relativeDest, $copy = false)
    {

        $dest = $this->getOption('path') . $relativeDest;
        $dirname = dirname($dest);

        if (!is_dir($dirname)) {
            $this->_mkdir(dirname($relativeDest));
        }

        if (!file_exists($dirname)) {
            throw new Centurion_Exception(sprintf('Directory %s does not exist', $dirname));
        }

        if (!is_writable($dirname)) {
            throw new Centurion_Exception(sprintf('File %s is not writable', $dirname));
        }

        if (!$copy) {
            $return = rename($source, $dest);
            if ($return)
                chmod($dest, 0777);
            return $return;
        }

        $return = copy($source, $dest);

        if ($return) {
            chmod($dest, 0777);
        }

        return $return;
    }
}
