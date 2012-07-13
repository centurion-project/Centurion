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
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 * @todo: This controller should be merge with FileController
 */
class Media_ImageController extends Centurion_Controller_Action
{
    public function preDispatch()
    {
        parent::preDispatch();

        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);
    }

    public function getAction()
    {
        $fileId = $this->_getParam('file_id');

        if (null === $fileId && $this->getRequest()->getServer('REDIRECT_QUERY_STRING')) {
            // Here, it's for an same apache server without urlrewriting
            list($id, $fileid, $key, $effect) = explode(':', $this->getRequest()->getServer('REDIRECT_QUERY_STRING'));
            $this->_request->setParam('id', $id);
            $this->_request->setParam('fileid', $fileId);
            $this->_request->setParam('key', $key);
            $this->_request->setParam('effect', $effect);

            // If the server don't have urlrewriting, he could (must?) use ErrorDocument 404 in .htaccess
            $this->getResponse()->setHttpResponseCode(200);
        }

        $fileId = bin2hex(Centurion_Inflector::urlDecode($this->_request->getParam('file_id')));
        $key = bin2hex(Centurion_Inflector::urlDecode($this->_request->getParam('key')));

        if (trim($this->_request->getParam('id')) == '') {
            $id = $fileId;
        } else
            $id = bin2hex(Centurion_Inflector::urlDecode($this->_request->getParam('id')));

        if (!($effectPath = $this->_request->getParam('effect'))) {
            return $this->_forward('get', 'file');
        }

        $media = Centurion_Config_Manager::get('media');

        $fileRow = $this->_helper->getObjectOr404('media/file', array('id' => $id));

        $mediaAdapter = Media_Model_Adapter::factory($media['adapter'],
                                                     $media['params']);

        $this->forward404If(!$mediaAdapter->isValidKey($fileRow,
                                                       $key,
                                                       $effectPath),
                            sprintf("key '%s' for file '%s' is not valid or expired", $key, $fileRow->pk));

        // TODO : modifier le file exist sur le bon chemin (cf getFullpath)
        if (!file_exists($media['uploads_dir']
                            . DIRECTORY_SEPARATOR
                            . $fileRow->local_filename)) {
            $this->_redirect('/layouts/backoffice/images/px.png', array('code' => 307));
        }

        $effects = Media_Model_DbTable_Image::effectsString2Array($effectPath);

        $imageAdapter = Centurion_Image::factory();

        $imagePath = sys_get_temp_dir()
                    . DIRECTORY_SEPARATOR
                   . uniqid();

        $imageAdapter->open($media['uploads_dir']
                            . DIRECTORY_SEPARATOR
                            . $fileRow->local_filename);
        foreach($effects as $key => $effect) {
            switch($key) {
//                case 'adaptiveresize':
//                    $effect = array_merge(array('width' => null, 'height' => null), $effect);
//                    $imageAdapter->adaptiveResize($effect['width'], $effect['height']);
//                    break;
                case 'adaptiveresize':
                    $effect = array_merge(array('width' => null, 'height' => null), $effect);
                    $imageAdapter->adaptiveResize($effect[1]['width'], $effect[1]['height']);
                    break;
                case 'cropcenter':
                    $effect = array_merge(array('width' => null, 'height' => null), $effect);
                    $imageAdapter->cropFromCenter($effect['width'], $effect['height']);
                    break;
                case 'resize':
                    $effect = array_merge(array('maxWidth' => null, 'maxHeight' => null, 'minWidth' => null, 'minHeight' => null), $effect);
                    $imageAdapter->resize($effect['maxWidth'], $effect['maxHeight'], $effect['minWidth'], $effect['minHeight']);
                    break;
                case 'crop':
                    $effect = array_merge(array('x' => null, 'y' => null, 'width' => null, 'height' => null), $effect);
                    $imageAdapter->crop($effect['x'], $effect['y'], $effect['width'], $effect['height']);
                    break;
                case 'cropcenterresize':
                    $effect = array_merge(array('width' => null, 'height' => null), $effect);
                    $imageAdapter->cropAndResizeFromCenter($effect['width'], $effect['height']);
                    break;
                case 'cropedgeresize':
                    $effect = array_merge(array('width' => null, 'height' => null, 'edge' => null), $effect);
                    $imageAdapter->cropAndResizeFromEdge($effect['width'], $effect['height'], $effect['edge']);
                    break;
                case 'IMG_FILTER_NEGATE':
                    $imageAdapter->effect('IMG_FILTER_NEGATE');
                    break;
                case 'IMG_FILTER_GRAYSCALE':
                    $imageAdapter->effect('IMG_FILTER_GRAYSCALE');
                    break;
                case 'IMG_FILTER_BRIGHTNESS':
                    $effect = array_merge(array('degree' => null), $effect);
                    $imageAdapter->effect('IMG_FILTER_BRIGHTNESS', $effect['degree']);
                    break;
                case 'IMG_FILTER_CONTRAST':
                    $effect = array_merge(array('degree' => null), $effect);
                    $imageAdapter->effect('IMG_FILTER_CONTRAST', $effect['degree']);
                    break;
                case 'IMG_FILTER_COLORIZE':
                    $effect = array_merge(array('red' => null, 'green' => null, 'blue' => null), $effect);
                    $imageAdapter->effect('IMG_FILTER_COLORIZE', $effect['red'], $effect['green'], $effect['blue']);
                    break;
                case 'IMG_FILTER_EDGEDETECT':
                    $imageAdapter->effect('IMG_FILTER_EDGEDETECT');
                    break;
                case 'IMG_FILTER_EMBOSS':
                    $imageAdapter->effect('IMG_FILTER_EMBOSS');
                    break;
                case 'IMG_FILTER_SELECTIVE_BLUR':
                    $imageAdapter->effect('IMG_FILTER_SELECTIVE_BLUR');
                    break;
                case 'IMG_FILTER_GAUSSIAN_BLUR':
                    $imageAdapter->effect('IMG_FILTER_GAUSSIAN_BLUR');
                    break;
                case 'IMG_FILTER_MEAN_REMOVAL':
                    $imageAdapter->effect('IMG_FILTER_MEAN_REMOVAL');
                    break;
                case 'IMG_FILTER_SMOOTH':
                    $imageAdapter->effect('IMG_FILTER_SMOOTH', $effect['degree']);
                    break;
                case 'IMG_FILTER_PIXELATE':
                    $imageAdapter->effect('IMG_FILTER_PIXELATE', $effect['size'], $effect['pixelate']);
            }
        }

        if (!is_dir(dirname($imagePath))) {
            mkdir(dirname($imagePath), 0777, true);
        }

        $imageAdapter->save($imagePath, $fileRow->mime);

        $isSaved = $mediaAdapter->save($imagePath, $fileRow->getRelativePath($effectPath, false, true));

        if ($isSaved) {
            Centurion_Db::getSingleton('media/duplicate')->insert(array(
                'file_id' => $fileRow->id,
                'adapter' => $media['adapter'],
                'params'  => serialize($media['params']),
                'dest'    => $fileRow->getRelativePath($effectPath, false, true)
            ));

            return $this->getHelper('redirector')->gotoUrlAndExit($fileRow->getStaticUrl($effectPath) . '&');
        }
        
        $offset = 24 * 60 * 60 * 365;
        $this->getResponse()->setHeader('Content-type', $fileRow->mime)
                            ->setHeader('Content-Length', filesize($imagePath))
                            ->setHeader('Content-Disposition', sprintf('inline; filename="%s";', $fileRow->filename))
                            ->setHeader('Cache-Control', sprintf('max-age=%d, public', $offset))
                            ->setHeader('Expires', sprintf('%s GMT', gmdate('D, d M Y H:i:s', time() + $offset)))
                            ->sendHeaders();

        while (@ob_end_flush());

        $fp = fopen($imagePath, 'rb');
        fpassthru($fp);
        fclose($fp);

        if (file_exists($imagePath))
            unlink($imagePath);

        $this->getResponse()->clearHeaders();
    }

    public function testAction()
    {
        $this->getHelper('layout')->enableLayout();
        $this->getHelper('viewRenderer')->setNoRender(false);
        $this->view->image = Centurion_Db::getSingleton('media/file')->random();
    }

    public function jsAction()
    {
        //See http://tinymce.moxiecode.com/wiki.php/Configuration:external_image_list_url
        $output = 'var tinyMCEImageList = new Array(';

        $mediaRowset = Centurion_Db::getSingleton('media/file')->fetchAll();

        foreach ($mediaRowset as $media) {
            $output .= PHP_EOL
                    . '["'
                    . utf8_encode($media->filename)
                    . '", "'
                    . utf8_encode($media->getStaticUrl())
                    . '"],';
        }

        $output = substr($output, 0, -1) . PHP_EOL; // remove last comma from array item list (breaks some browsers)

        $output .= ');';

        header('Content-type: text/javascript');
        header('pragma: no-cache');
        header('expires: 0');

        echo $output;
    }

    public function _getFile($fileId, $contentType = 'Media_Model_DbTable_Image')
    {
        return $this->_helper->getObjectOr404('media/file', array('id'          =>  $fileId,
                                                                  'proxy_model' =>  $contentType));
    }
}
