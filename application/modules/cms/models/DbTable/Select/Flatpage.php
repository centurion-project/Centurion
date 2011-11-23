<?php

class Cms_Model_DbTable_Select_Flatpage extends Centurion_Db_Table_Select
{
    public function wherePublished($isPublished = true)
    {
        $operator = $isPublished ? '<':'>';
        
        return $this->where(sprintf('cms_flatpage.published_at %s ?', $operator), new Zend_Db_Expr('NOW()'))
                    ->where('cms_flatpage.is_published = ?', $isPublished);
    }
}
