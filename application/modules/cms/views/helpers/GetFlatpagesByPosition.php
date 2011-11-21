<?php

class Cms_View_Helper_GetFlatpagesByPosition extends Zend_View_Helper_Abstract
{
    public function getFlatpagesByPosition($key, $depth = null, $limit = null, $order = 'mptt_lft')
    {
        $flatpageTable = Centurion_Db::getSingleton('cms/flatpage');
        Centurion_Cache_TagManager::addTag($flatpageTable);
        return $flatpageTable->getFlatpagesByPosition($key, $depth, $limit, $order);
    }
}