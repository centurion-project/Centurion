<?php

class Media_Form_Model_Admin_File2 extends Media_Form_Model_Admin_File
{

    protected $_exclude = array(
        'created_at', 'use_count', 'height', 'width', 'filesize', 'mime', 'local_filename', 'id', 'sha1', 'user_id',
        'belong_model', 'belong_pk', 'proxy_pk', 'proxy_model', 'description', 'name', 'filename'
    );

    public function __construct()
    {
        $this->_elementLabels['tags'] = 'Tags';
        parent::__construct();
    }

    public function init()
    {
        // add field for adding new tags on the fly
        $newtags = new Zend_Form_Element_Text('newtags');
        $newtags->setLabel('Add tags')->setDescription('Example: tag, tag, tag, tag');
        $this->addElement($newtags, 'newtags');

        $this->moveElement('tags', self::LAST);

        //TODO: remove this when the moveElement will be fixed
        $tags = $this->getElement('tags');
        $this->removeElement('tags');
        $this->addElement($tags);

        parent::init();
    }

    public function saveInstance($values = null)
    {
        if ($values === null) {
            $values = $this->getValues();
        }
        $posted_at = new Zend_Date($values['published_at'], 'MM/dd/yy');
        $values['published_at'] = $posted_at->get('YYYY-MM-dd HH:mm:ss');

        $instance = parent::saveInstance($values);

        if ('' !== trim($this->getElement('newtags')->getValue())) {
            $this->_saveNewTags($instance);
        }
        return $instance;
    }

    protected function _saveNewTags($instance)
    {
        // Get post id
        $fileId = $instance->id;

        // Get all new tags
        $arrTags = explode(',', $this->getElement('newtags')->getValue());

        $tagTable = Centurion_Db::getSingleton('media/tag');
        $itemTagTable = Centurion_Db::getSingleton('media/tagFile');

        // Get or create tag & insert the relation
        foreach ($arrTags as $tag) {
            $tag = trim($tag);
            if ('' === $tag) {
                continue;
            }
            // Get or create tag
            $data = array('name' => $tag);
            list($newTagRow,) = $tagTable->getOrCreate($data);

            // Get or create the relation Item <--> Tag
            $data = array(
                'file_id' => $fileId,
                'tag_id'  => $newTagRow->id
            );

            $itemTagTable->insertIfNotExist($data);
        }
    }

    public function _onPopulateWithInstance()
    {
        $instance = $this->getInstance();
        $info = new Centurion_Form_Element_Info(array(
            'disableTranslator' => true,
            'name'  => 'thumbnail',
            'label' => null,
            'value' => '<img class="image-preview" src="'.$instance->getStaticUrl().'" />',
            'escape' => false
        ), 'thumbnail');
        $info->setAttrib('large', true);
        $this->addElement($info, 'thumbnail');

        $this->removeElement($this->getFilename()->getName());
    }

}
