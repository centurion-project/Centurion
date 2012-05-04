<?php

class Cms_Form_Model_Flatpage extends Centurion_Form_Model_Abstract implements Translation_Traits_Form_Model_Interface
{
    public function __construct($options = array(), Centurion_Db_Table_Row_Abstract $instance = null)
    {
        $this->_model = Centurion_Db::getSingleton('cms/flatpage');

        $this->_exclude = array('created_at', 'id', 'updated_at', 'mptt_lft', 'mptt_rgt',
                                'slug', 'mptt_level', 'mptt_tree_id', 'faltpage_type', 'cover_id', 'mptt_parent_id');

        $this->_elementLabels = array(
            'title'                     => $this->_translate('Title'),
            'description'               => $this->_translate('Description'),
            'keywords'                  => $this->_translate('Keywords'),
            'url'                       => $this->_translate('URL'),
            'body'                      => $this->_translate('Content'),
            'cover_id'                  => $this->_translate('Cover'),
            'flatpage_template_id'      => $this->_translate('Template'),
            'mptt_parent_id'            => $this->_translate('Parent'),
            'published_at'              => $this->_translate('Date to publish'),
            'is_published'              => $this->_translate('Is published'),
            'order'                     => $this->_translate('Order'),
            'route'                     => $this->_translate('Route'),
        );

        parent::__construct($options, $instance);
    }

    public function init()
    {
        parent::init();

        $this->setLegend($this->_translate('Edit flatpage'));

        $this->getElement('body')->setAttrib('class', 'field-rte')
                                 ->setAttrib('large', true)
                                 ->removeFilter('StripTags');

        $this->getElement('flatpage_template_id')->setAttrib('large', true);

        //$this->getElement('mptt_parent_id')->setAttrib('large', true);

        $this->getElement('published_at')->setAttrib('large', true);
        $this->getElement('published_at')->setRequired(false)->setAttrib('class', 'field-datetimepicker');;

        $this->getElement('url')->addValidator(new Centurion_Form_Model_Validator_AlreadyTaken('cms/flatpage', 'url'));

        $this->addElement('info', 'created_at', array('label' => 'Created at'));
        $this->addElement('info', 'updated_at', array('label' => 'Updated at'));

        $pic = new Media_Form_Model_Admin_File(array('name' => 'cover'));
        $pic->getFilename()->getValidator('Size')->setMax(4*1024*1024);
        $this->addReferenceSubForm($pic, 'cover');
    }

    public function isValid($data)
    {
        if (!$data['url']) {
            if ($this->hasInstance()) {
                $data['url'] = '/' . $this->getInstance()->slug;
            } elseif ($data['title']) {
                $data['url'] = '/' . Centurion_Inflector::slugify($data['title']);
            }
        }

        $params = array();

        // @todo: should be done with a trait ?
        if (isset($data['language_id'])) {
            $params['language_id'] = $data['language_id'];
        }

        $this->getElement('url')->getValidator('Centurion_Form_Model_Validator_AlreadyTaken')
                                 ->mergeParams($params);



        return parent::isValid($data);
    }

    public function setInstance(Centurion_Db_Table_Row_Abstract $instance = null)
    {
        parent::setInstance($instance);

        if ($instance !== null) {
            if ($instance->isVisible())
                $this->addElement('info', 'link_front', array('escape' => false, 'label' => 'Show it in front', 'value' => '<a href="' . $instance->permalink . '">Show</a>'));
        }
        return $this;
    }

    protected function _onPopulateWithInstance($instance = null)
    {
        parent::_onPopulateWithInstance($instance);

        if ($this->hasInstance()) {

            $this->getElement('url')->getValidator('Centurion_Form_Model_Validator_AlreadyTaken')
                                    ->mergeParams(array('!id' => $this->getInstance()->pk,
                                                        // @todo: should be done with a trait ?
                                                        'language_id' => $this->getInstance()->language_id));
        }
    }

    protected function _postGenerate()
    {
         array_splice($this->_exclude, -2, 2);
    }
}