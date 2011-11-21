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
 * @subpackage  Media
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Media
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Media_Form_Model_File extends Centurion_Form_Model_Abstract
{
    protected $_exclude = array('created_at', 'use_count', 'height', 'width', 'filesize', 'mime', 'local_filename', 'id', 'sha1',
                                'user_id', 'belong_model', 'belong_pk', 'proxy_pk', 'proxy_model', 'description', 'name', 'filename');

    public function __construct($options = array())
    {
        $this->setModel(Centurion_Db::getSingleton('media/file'))
             ->setEnctype(self::ENCTYPE_MULTIPART);

        parent::__construct($options);
    }
}