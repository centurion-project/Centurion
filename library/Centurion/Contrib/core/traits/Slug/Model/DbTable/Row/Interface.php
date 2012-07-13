<?php

interface Core_Traits_Slug_Model_DbTable_Row_Interface
{

    /**
     * @abstract
     * @return array[]string
     */
    public function getSlugifyName();
    
    /**
     * A function named getFilterFieldsForSlug could be defined in the row,
     * to filter the search of duplicate field by a given column.
     * Exemple if my table have a category_id, i could tell slug trait, to have uniq slug in a category.
     * In that case 2 row, of different category, could have the same slug.
     * 
     * @return array name column to filter the search of duplicated slug
     */
    //public function getFilterFieldsForSlug();
}
