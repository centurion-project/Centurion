<?php
class Centurion_Traits_Queue extends Centurion_Queue
{
    public function push($trait)
    {
        if (!$trait instanceof Centurion_Traits_Abstract) {
            throw new Centurion_Traits_Exception('Centurion_Trait_Queue can only handle trait object');
        }
        
        parent::push($trait);
        
        try {
            $trait->init();
        } catch (Centurion_Traits_Exception $e) {
            parent::offsetUnset(parent::count() - 1);
            throw $e;
        }
    }
    
}
