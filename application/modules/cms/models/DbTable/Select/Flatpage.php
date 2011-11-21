<?php

class Cms_Model_DbTable_Select_Flatpage extends Centurion_Db_Table_Select
{
    public function wherePublished($isPublished = true)
    {
        $operator = $isPublished ? '<':'>';
        
        return $this->where(sprintf('cms_flatpage.published_at %s ?', $operator), new Zend_Db_Expr('NOW()'))
                    ->where('cms_flatpage.is_published = ?', $isPublished);
    }
    
    public function wherePosition($position)
    {
        if ($position instanceof Cms_Model_DbTable_Row_FlatpagePosition) {
            $position = (int) $position->pk;
        }
        
        if (is_int($position)) {
            $this->where('cms_flatpage.flatpage_position_id = ?', $position);
        } else if (is_string($position)) {
            $this->join('cms_flatpage_position', 'cms_flatpage.flatpage_position_id = cms_flatpage_position.id', array())
                 ->where('cms_flatpage_position.key = ?', $position);
        }
        
        return $this;
    }
}
