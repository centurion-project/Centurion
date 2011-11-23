<?php
class Centurion_View_Helper_FormFileTable extends Zend_View_Helper_FormFile
{
    public function formFileTable($name, $attribs = null)
    {
        return $this->formFile($name, $attribs);
    }
}