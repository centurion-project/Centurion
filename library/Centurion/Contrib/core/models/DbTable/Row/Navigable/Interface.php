<?php

interface Core_Model_DbTable_Row_Navigable_Interface
{
    public function __toString();
    public function isPublished();
    public function getPublishedAt();
    public function isVisible($identity = null);
}