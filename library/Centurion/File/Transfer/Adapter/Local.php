<?php

class Centurion_File_Transfer_Adapter_Local extends Centurion_File_Transfer_Adapter_Abstract
{
    /**
     * Adds one or more files
     *
     * @param  string|array $file      File to add
     * @param  string|array $validator Validators to use for this file, must be set before
     * @param  string|array $filter    Filters to use for this file, must be set before
     * @return Zend_File_Transfer_Adapter_Abstract
     * @throws Zend_File_Transfer_Exception Not implemented
     */
    public function addFile($files, $validator = null, $filter = null)
    {
        foreach ($files as $name => $file) {
            $this->_files[$name] = array(
                'size'      => filesize($file),
                'tmp_name'  => $file,
                'name'      => basename($file),
            );
            
            $this->_files[$name]['type'] = $this->_detectMimeType($this->_files[$name]);
        }
        
        return $this;
    }
}