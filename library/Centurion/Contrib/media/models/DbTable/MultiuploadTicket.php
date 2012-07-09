<?php

class Media_Model_DbTable_MultiuploadTicket extends Centurion_Db_Table_Abstract
{
    /*
     * TTL in second
     * @var int
     */
    protected $_ttl = 600;

    protected $_primary = 'id';

    protected $_name = 'media_multiupload_ticket';

    protected $_referenceMap = array(
        'form_class_model'   =>  array(
            'columns'       => 'form_class_model_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Core_Model_DbTable_ContentType'
        ),
        'proxy_model'   =>  array(
            'columns'       => 'proxy_model_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Core_Model_DbTable_ContentType'
        ),
    );

    public function createTicket($form, $elementName)
    {
        if (mt_rand(0, 500) === 8) {
            $this->delete(array(new Zend_Db_Expr('expire < NOW()')));
        }

        $instance = $form->getInstance();

        $proxyModel = null;
        $proxyPk = null;

        if (null !== $instance) {
            list($proxyModel, $created) = Centurion_Db::getSingleton('core/contentType')->getOrCreate(array('name' => get_class($instance->getTable())));
            $proxyPk = $instance->pk;
            $proxyModel = $proxyModel->id;
        }

        list($classNameModel, $created) = Centurion_Db::getSingleton('core/contentType')->getOrCreate(array('name' => get_class($form)));

        $classNameModel = $classNameModel->id;

        return $this->insert(
            array(
                self::RETRIEVE_ROW_ON_INSERT => true,
                'ticket'                     => md5(uniqid()),
                'expire'                     => new Zend_Db_Expr('DATE_ADD(NOW(), INTERVAL ' . $this->_ttl . ' SECOND)'),
                'proxy_model_id'             => $proxyModel,
                'proxy_pk'                   => $proxyPk,
                'form_class_model_id'        => $classNameModel,
                'element_name'               => $elementName,
                'values'                     => serialize($form->getValues()),
            )
        );
    }
}
