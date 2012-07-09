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
 * @author      Laurent Chenay <lc@centurion-project.org>
 * @todo: This controller should be merge with ImageController
 */
class Media_FileController extends Centurion_Controller_Action
{
    public function getAction()
    {
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        $media = $this->getInvokeArg('bootstrap')->getOption('media');
        if (!(null != ($fileRow = $this->_getParam('file')) && $fileRow instanceof Media_Model_DbTable_Row_File)) {
            $mediaAdapter = Media_Model_Adapter::factory($media['adapter'],
                                                         $media['params']);

            $id = bin2hex(Centurion_Inflector::urlDecode($this->_request->getParam('id')));
            $key = bin2hex(Centurion_Inflector::urlDecode($this->_request->getParam('key')));

            $fileRow = $this->_helper->getObjectOr404('media/file', array('id' => $id));

            $this->forward404If(!$mediaAdapter->isValidKey($fileRow,
                                                           $key,
                                                           '',
                                                           $media['key_lifetime']));
        }
            $source = $media['uploads_dir']
                    . DIRECTORY_SEPARATOR
                    . $fileRow->local_filename;

        if (null == $this->_getParam('noAdapter', null)) {
            $isSaved = $mediaAdapter->save($source, $fileRow->getRelativePath(null, false, true), true);

            if ($isSaved) {
                Centurion_Db::getSingleton('media/duplicate')->insert(array(
                    'file_id' => $fileRow->id,
                    'adapter' => $media['adapter'],
                    'params'  => serialize($media['params']),
                    'dest'    => $fileRow->getRelativePath(null, false, true)
                ));

                return $this->getHelper('redirector')->gotoUrlAndExit($fileRow->getStaticUrl());
            }
        }

        $offset = 24 * 60 * 60 * 365;
        $this->getResponse()->setHeader('Content-type', $fileRow->mime)
                            ->setHeader('Content-Length', filesize($source))
                            ->setHeader('Content-Disposition', sprintf('inline; filename="%s";', $fileRow->filename))
                            ->setHeader('Cache-Control', sprintf('max-age=%d, public', $offset))
                            ->setHeader('Expires', sprintf('%s GMT', gmdate('D, d M Y H:i:s', time() + $offset)))
                            ->sendHeaders();

        while (@ob_end_flush());

        $fp = fopen($source, 'rb');
        fpassthru($fp);
        fclose($fp);
        $this->getResponse()->clearHeaders();
    }

    public function contentAction()
    {
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);
        $offset = 24 * 60 * 60 * 365;
        $this->getResponse()->setHeader('Content-type', $this->_getParam('mime'))
                            ->setHeader('Content-Length', $this->_getParam('filesize'))
                            ->setHeader('Content-Disposition', sprintf('inline; filename="%s"', $this->_getParam('filename')))
                            ->setHeader('Cache-Control', sprintf('max-age=%s, public', $offset))
                            ->setHeader('Expires', sprintf("%s GMT", gmdate('D, d M Y H:i:s', time() + $offset)))
                            ->setBody($this->_getParam('content'));
    }
}
