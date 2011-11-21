<?php
class Admin_AdminDashboardController extends Centurion_Controller_Action
{
   public function preDispatch()
   {
       $this->_helper->authCheck();
       $this->_helper->aclCheck();
       
       parent::preDispatch();
   }
   
   public function index()
   {
       
   }
}
