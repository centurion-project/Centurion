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
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Media_UploadController extends Centurion_Controller_Action
{
    public function uploadAction()
    {
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        $ticket = $this->_getParam('uploadTicket');
        $ticket = Centurion_Db::getSingleton('media/multiupload_ticket')->findOneByTicket($ticket);

        if (null === $ticket) {
            echo Zend_Json::encode(array('code' => false, 'message' => 'Invalid Ticket'));
            return;
        }

        $originalForm = new $ticket->form_class_model->name();

        if (null !== $ticket->proxy_pk) {
            $instance = Centurion_Db::getSingletonByClassName($ticket->proxy_model->name)->find($ticket->proxy_pk);
            $instance = $instance->current();
            $originalForm->setInstance($instance);
        }

        if ($ticket->values !== null) {
            $originalForm->populate(unserialize($ticket->values));
        }

        $result = array();

        $form = $originalForm->getElement($ticket->element_name)->getFile();

        if ($form->isValid(array())) {
            $file = $form->save();
            if (method_exists($originalForm, 'postMultiuploadSuccess')) {
                $originalForm->postMultiuploadSuccess($file);
            }

            $result['code'] = true;
            if ($file->isImage())
                $result['thumb'] = $file->getStaticUrl(array('resize' => array('maxWidth' => 100, 'maxHeight' => 100)));
            $result['fileId'] = $file->id;
        } else {
            $result['code'] = false;
            $result['message'] = $form->getErrorMessages();
        }

        echo Zend_Json::encode($result);
    }
}
