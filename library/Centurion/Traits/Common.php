<?php
class Centurion_Traits_Common
{
    /**
     * 
     * Enter description here ...
     * @param Centurion_Traits_Traitsable $object
     * @todo: optimize
     * @optimize
     */
    static public function initTraits(Centurion_Traits_Traitsable $object)
    {
        foreach (class_implements($object) as $implement) {
            self::addTraits($object, $implement);
        }
    }

    static public function addTraits(Centurion_Traits_Traitsable $object, $implement)
    {
        $traitQueue = $object->getTraitQueue();

        //TODO: change this preg match. Too permissive
        if (preg_match('`(.*_Traits.*)_Interface`', $implement, $matches)) {
            $className = $matches[1];
            if (class_exists($className, true) && in_array('Centurion_Traits_Abstract', class_parents($className))) {
                $traitQueue->push(new $className($object));
            }
        }
    }

    static public function checkTraitOverload(Centurion_Traits_Traitsable $object, $method, $args = array(), $stopOnFound = true)
    {
        $traitQueue = $object->getTraitQueue();

        $methodName = strstr($method, '::');
        if (false !== $methodName) {
            $method = substr($methodName, 2);
        }

        $found = false;
        $retVal = null;
        
        foreach ($traitQueue as $trait) {
            if (method_exists($trait, $method)) {
                $retVal = call_user_func_array(array($trait, $method), (array) $args);
                $found = true;
                if ($stopOnFound) {
                    break;
                }
            }
        }

        return array($found, $retVal);
    }


    static public function checkTraitPropertyExists(Centurion_Traits_Traitsable $object, $property)
    {
        $traitQueue = $object->getTraitQueue();

        foreach ($traitQueue as $trait) {
            if ($trait->defineProperty($property)) {
                return $trait;
            }
        }

        return null;
    }

}
