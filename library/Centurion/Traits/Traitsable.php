<?php

interface Centurion_Traits_Traitsable extends Centurion_Delegate_Interface
{
    public function getTraitQueue();

    public function __get($column);
    public function __set($column, $value);
    public function __isset($column);
    public function __unset($column);
    //public function __call($function, $args);

}
