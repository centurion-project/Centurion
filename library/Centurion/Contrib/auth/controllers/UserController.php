<?php

/**
 * Currently in heavy development
 */
class Auth_UserController extends Centurion_Controller_Action
{
    # 4 views for password reset:
    # - password_reset sends the mail
    # - password_reset_done shows a success message for the above
    # - password_reset_confirm checks the link the user clicked and 
    #   prompts for a new password
    # - password_reset_complete shows a success message for the above
    public function passwordResetAction()
    {
        $viewScript = $this->_getParam('view_script', 'registration/password_reset_form.phtml');
        $emailViewScript = $this->_getParam('email_view_script', 'registration/password_reset_email.txt');
        $passwordResetForm = $this->_getParam('form_class', 'Auth_Form_PasswordReset'); 
        $postResetRedirect = $this->_getParam('post_reset_redirect', $this->_helper->url('password-reset-done', 'user', 'auth'));
        
        $form = new $passwordResetForm();
        if ($this->getRequest()->isPost()) {
            $posts = $this->getRequest()->getPost();
            $opts = array('useHttps' => $this->getRequest()->isSecure());
            if ($form->isValid($posts)) {
                $form->save($opts);
                
                return $this->getHelper('redirector')->gotoUrlAndExit($postResetRedirect);
            } else {
                $form->populate($posts);
            }
        }
        
        return $this->renderToResponse($viewScript, array('form' => $form));
    }
    
    public function passwordResetDoneAction()
    {
        return $this->renderToResponse($this->_getParam('view_script', 'registration/password_reset_form.phtml'));
    }
}