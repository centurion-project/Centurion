<?php

class Media_View_Helper_GetFilesFor extends Zend_View_Helper_Abstract
{
    public function getFilesFor($object)
    {
        return Centurion_Db::getSingleton('media/file')->getFilesFor($object);
    }
}
