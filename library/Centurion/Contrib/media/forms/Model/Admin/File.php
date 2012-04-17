<?php

class Media_Form_Model_Admin_File extends Media_Form_Model_File
{
    protected $_extension = 'jpg,png,gif,jpeg';

    protected $_fileDescription = 'Images files';

    public $useDefaultDecorator = true;
    /**
     * @var Centurion_Form_Element_FileTable
     */
    protected $_filename = null;

    public function init()
    {
        $options = Centurion_Config_Manager::get('media');

        $this->_filename = new Centurion_Form_Element_FileTable(sprintf('filename_%s', $this->getName()));

        $this->_filename->getPluginLoader(Zend_Form_Element_File::TRANSFER_ADAPTER)->addPrefixPath('Centurion_File_Transfer_Adapter', 'Centurion/File/Transfer/Adapter/');

        if (null !== $this->getAttrib('adapter'))
            $this->_filename->setTransferAdapter($this->getAttrib('adapter'));

        $this->_filename->setDestination($options['uploads_dir'])
                 ->addValidator('Count', false, 1)
                 ->addValidator('Size', false, 4194304) //4*1024*1024
                 ->addValidator('Extension', false, $this->_extension);

        $this->addElement($this->_filename);

        $this->removeElement('_XSRF');
        parent::init();
    }

    public function __call($name, $args)
    {
        $prefix = substr($name, 0, 3);
        if (in_array($prefix, array('get', 'set')) && method_exists($this->_filename, $name))
            return call_user_func_array(array($this->_filename, $name), $args);
        else
            throw new Exception (sprintf("Method %s doesn't exists and was not trapped in __call", $name));
    }

    public function getExtension()
    {
        return $this->_extension;
    }

    /**
     * @param string $extension
     * @return $this
     */
    public function setExtension($extension)
    {
        $this->_extension = $extension;
        $this->_filename->getValidator('Extension')->setExtension($extension);

        return $this;
    }

    /**
     * @param string $extension
     * @return $this
     */
    public function addExtension($extension)
    {
        $this->_extension .= ','.$extension;
        $this->_filename->getValidator('Extension')->addExtension($extension);

        return $this;
    }

    public function getFileDescription()
    {
        return $this->_fileDescription;
    }

    public function isValid($data)
    {
        // this is how we check if something went wrong while uploading
        // i.e. post_max_size was exceeded
        if (!isset($_FILES[$this->getFilename()->getName()])) {
            $this->getFilename()->addError('There was a problem uploading this file. Check max size allowed');
            return false;
        }

        $isValid = parent::isValid($data);

        if ($isValid && $this->_filename->receive()) {
            if ($this->_filename->isUploaded()) {
                return true;
            }
        }

        if ($isValid && !$this->_filename->receive()) {
            $messages = $this->_filename->getMessages();
            if (count($messages) == 1 && isset($messages[Zend_Validate_File_Upload::NO_FILE])) {
                if ($this->hasInstance() && $this->getElement(sprintf('filename_delete_%s', $this->getName()))->isChecked()) {
                    return true;
                }
            }
        }

        return $isValid;
    }
    /**
     * @return Centurion_Form_Element_FileTable
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    public function getValues($suppressArrayNotation = false)
    {
        if ($this->hasInstance() && null !== $this->getElement(sprintf('filename_delete_%s', $this->getName()))) {
            if (!$this->getElement(sprintf('filename_delete_%s', $this->getName()))->isChecked()) {
                $this->getInstance()->delete();
                $this->setInstance(null);
            }
        }

        if ($this->_filename->isUploaded()) {
            return $this->_filename->getValues();
        }

        return false;
    }

    public function postCleanAsSubform()
    {
        if ($this->useDefaultDecorator)
            $this->setDecorators(array(array('ViewScript', array('viewScript' => 'form/admin/_file.phtml'))));
    }

    protected function _onPopulateWithInstance()
    {
        $name = sprintf('filename_delete_%s', $this->getName());
        $this->addElement('checkbox', $name, array('name' => $name, 'class' => 'field-checkbox', 'value' => true, 'checkedValue' => true, 'uncheckedValue' => false));
    }
}
