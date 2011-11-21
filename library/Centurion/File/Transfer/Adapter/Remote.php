<?php

class Centurion_File_Transfer_Adapter_Remote extends Centurion_File_Transfer_Adapter_Abstract 
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
            $dest = tempnam(sys_get_temp_dir(), 'transfert');
            
            $client = new Zend_Http_Client($file, array('adapter' => 'Zend_Http_Client_Adapter_Curl'));
            $response = $client->setStream($dest)->request(Zend_Http_Client::GET);
            
            $this->_files[$name] = array(
                'size'      => filesize($dest),
                'tmp_name'  => $dest,
                'name'      => basename($file),
            );
            
            $this->_files[$name]['type'] = $this->_detectMimeType($this->_files[$name]);
        }
        
        return $this;
    }
}