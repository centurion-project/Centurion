<?php
/**
 * @class Translation_Test_Traits_Model_DbTable_Row_SecondModel
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
 *      - title (non-translatable)
 *      - content (translatable)
 *      - is_active (non translatable)
 *
 * A dependant relation : first
 *
 */
class Translation_Test_Traits_Model_DbTable_Row_SecondModel
    extends Centurion_Db_Table_Row_Abstract
    implements Translation_Traits_Model_DbTable_Row_Interface{

}
