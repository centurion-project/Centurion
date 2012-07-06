<?php
/**
 * @class Translation_Test_Traits_Model_DbTable_Row_FirstModel
 * @package Tests
 * @subpackage Translatable
 * @author Richard Déloge, rd@octaveoctave.com
 *
 * Model of test to check is the behavior of the trait translation is the excepted behavior.
 *
 * This model contains 7 fields :
 *      - id
 *      - original_id
 *      - language_id
 *      - title (translatable)
 *      - content (translatable)
 *      - second1_id (transltable)
 *      - second2_id (non-translatable)
 *      - is_active (non-translatable)
 *
 * Two parent relations :
 *      - second1 (translatable)
 *      - second2 (non-translatable)
 *
 * Two dependents relations ;
 *      - thirds
 *      - fourths
 *
 */
class Translation_Test_Traits_Model_DbTable_Row_FirstModel
        extends Centurion_Db_Table_Row_Abstract
        implements Translation_Traits_Model_DbTable_Row_Interface{

    /**
     * @static
     * Added to clean relationship cache to execute suite test (needed because the dbms is restored before each cache)
     */
    public static function cleanLocalReferenceCache(){
        self::$_relationship = array();
    }
}
