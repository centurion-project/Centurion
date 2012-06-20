<?php
class Centurion_Traits_Queue extends Centurion_Queue{
    /**
     * Store each trait uniq id in this stack to test
     * if a trait object in is this queue (by isAllowedContext),
     * a isset($this->_traitQueueInQueue[uniqueId])
     *  is more efficient that in_array($myObject, iterator_to_array($this), true)
     *
     * The uniq id is done by Centurion_Traits_Abstract from a private state counter
     * @var int[]
     */
    protected $_traitQueueInQueue = array();

    public function push($trait)
    {
        if (!$trait instanceof Centurion_Traits_Abstract) {
            throw new Centurion_Traits_Exception('Centurion_Trait_Queue can only handle trait object');
        }
        parent::push($trait);

        $this->_traitQueueInQueue[$trait->getUniqueId()] = 1;

        try {
            $trait->init();
        } catch (Centurion_Traits_Exception $e) {
            parent::offsetUnset(parent::count() - 1);
            throw $e;
        }
    }

    public function inQueue(Centurion_Traits_Abstract $object){
        if(isset($this->_traitQueueInQueue[$object->getUniqueId()]))
            return true;

        return false;
    }
}
