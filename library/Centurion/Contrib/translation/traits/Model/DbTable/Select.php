<?php
/**
 * @class Translation_Traits_Model_DbTable_Select
 * Trait to support translation  versionning
 *
 * @package Centurion
 * @subpackage Translation
 * @author Mathias Desloges, Laurent Chenay, Richard DELOGE, rd@octaveoctave.com
 * @copyright Octave & Octave
 */
class Translation_Traits_Model_DbTable_Select
        extends Centurion_Traits_Model_DbTable_Select_Abstract
{
    const CHILD_PREFIX = 'child_';

    /**
     * To store the name of this ttable
     * @var string
     */
    protected $_selectTableName=null;

    /**
     * To store getTranslationSpec of this table
     * @var array
     */
    protected $_translationSpec=null;

    /**
     * To store the result of $this->getTable() instanceof Translation_Traits_Model_DbTable_Interface
     * @var bool
     */
    protected $_tableImplementsTraits=false;

    /**
     * @var string
     */
    protected $_localizedPrefix=null;

    /**
     * To store the value argument of the method _where
     * @var mixed
     */
    protected $_value = null;

    /**
     * To store all the request of the method _where
     * @var string
     */
    protected $_request = null;

    /**
     * Extrem optimisation of _checkWhereTranslation (it call a lot of many time)
     * @var array
     */
    protected $_replacementColumnAlreadyFetched = array();

    /**
     * To check if the model associated with this select object support the trait translation or not
     * and disable it if not
     * @return bool
     */
    protected function _tableImplementsTranslation(){
        if(null == $this->_tableImplementsTraits){
            //To record if the model is translatable or not. (Do not this operation several times
            $this->_tableImplementsTraits =
                ($this->getTable() instanceof Translation_Traits_Model_DbTable_Interface);
        }

        return $this->_tableImplementsTraits;
    }

    /**
     * To return the SQL name of the table associated with this select object
     * @return null|string
     */
    protected function _getSelectTableName(){
        //Retrieve and store the SQL name of the table
        if(null == $this->_selectTableName){
            if($table = $this->_select->getTable()){
                $this->_selectTableName = $table->info(Centurion_Db_Table_Abstract::NAME);
            }
        }

        return $this->_selectTableName;
    }

    /**
     * To return the list of translatable field
     * @return string[]
     */
    protected function _getTransaltionSpec(){
        //Get translation information for the current table
        if(null == $this->_translationSpec){
            $_translationSpec = $this->_select->getTable()->getTranslationSpec();
            //To remove translatable relations
            $_tableColumns = $this->_table->info(Centurion_Db_Table_Abstract::COLS);

            if(!empty($_translationSpec[Translation_Traits_Model_DbTable::TRANSLATED_FIELDS])){
                $this->_translationSpec = array_intersect(
                    $_translationSpec[Translation_Traits_Model_DbTable::TRANSLATED_FIELDS],
                    $_tableColumns
                );
            }
            else{
                $this->_translationSpec = array();
            }
        }

        return $this->_translationSpec;
    }

    /**
     * Initialize this trait
     */
    public function init(){
        Centurion_Signal::factory('on_select_joinInner')
            ->connect(
                array($this, 'onJoinInner'),
                $this->_select
            );


        $fromParts = $this->_select->getPart('from');
        if (count($fromParts)) {
            $from = current($fromParts);

            $this->onJoinInner(null, null, null, $from['tableName']);
        }
    }

    /**
     * add filters to the default select query
     * @see Centurion/Contrib/core/traits/Version/Model/Core_Traits_Version_Model_DbTable::onSelect()
     */
    public function onJoinInner($signal, $sender, $select, $name)
    {
        // - avoid updating sql query if concerned model is not translated
        if (!$this->_tableImplementsTranslation()
            || null === $this->_getSelectTableName()) {
            return;
        }

        // - avoid recursion
        if ($name !== $this->_getSelectTableName()) {
            return;
        }

        // - avoid updating sql query if filters are disabled
        if (!Centurion_Db_Table_Abstract::getFiltersStatus()) {
            return;
        }

        //Retrieve the corellation name if the request. (If it is empty, so, there are not corellation and
        //the table is used under its true name)
        $corellationName = 0;
        if (is_array($name)) {
            $corellationName = key($name);
            $name = current($name);
        }

        if (0 === $corellationName){
            $corellationName = $name;
        }

        $childName = self::CHILD_PREFIX . $corellationName;//$this->_modelName;

        //This table is already translated in the request, exit
        if (array_key_exists($childName, $this->getPart(Centurion_Db_Table_Select::FROM))) {
            return;
        }

        $this->setIntegrityCheck(false);

        $currentLanguage = Translation_Model_DbTable_Language::getCurrentLanguageInfo();

        $originalCols = array();
        $childCols = array();

        if(null == $this->_localizedPrefix){
            $this->_localizedPrefix = $this->getTable()->getLocalizedColsPrefix();
        }

        //Generate translated fields for this table
        $_translatedFields = array_merge(
                $this->_getTransaltionSpec(),
                $this->_table->info(Centurion_Db_Table_Abstract::PRIMARY), //Add localized id
                array(
                    Translation_Traits_Model_DbTable::ORIGINAL_FIELD,
                    Translation_Traits_Model_DbTable::LANGUAGE_FIELD
                ) //To return the language of the localized row
            );

        foreach ($_translatedFields as $col) {
            $childCols[] = $childName.'.'.$col.' AS '.$this->_localizedPrefix.$col;
            $originalCols[] = $this->_getSelectTableName().'.'.$col;
        }

        //If the request object was not build with the current form (so, with $myModel->select(false)
        if (!array_key_exists($this->_getSelectTableName(), $this->getPart(Zend_Db_Select::FROM))){
            $this->from($this->_getSelectTableName(), new Zend_Db_Expr(implode(', ', $originalCols)));
        }

        //Join to the original row the translated row
        $this->where($this->_getSelectTableName().'.original_id IS NULL');
        $method = 'joinLeft';

        try {
            //Add the related join and translated columns
            $this->{$method}(
                $this->_getSelectTableName().' AS ' . $childName,
                    new Zend_Db_Expr($childName . '.original_id = ' . $this->_getSelectTableName() .'.id'
                        .' AND ' . $childName . '.language_id = '.intval($currentLanguage['id'])),
                    new Zend_Db_Expr(implode(', ', $childCols))
            );

        } catch (Exception $e) {
            error_log($e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL.$this->__toString());
            throw $e;
        }

        //If we must select the original row if there are not localized row or not
        if (!$this->_table->ifNotExistsGetDefault()) {
            $this->where(
                new Zend_Db_Expr($childName . '.language_id = '.intval($currentLanguage['id'])
                    .' OR '.$this->_getSelectTableName().'.language_id = '.intval($currentLanguage['id'])
                )
            );
        }
    }

    /**
     * Called by preg_replace_callback in _where to replace tanslatable method name to ifnull(translatable, original).
     * Warning, for all column called "slug", the method not transform COL = VAL to ifnull(CHILD.COL, ORI.COL) = VAL
     *  but, it transforms to (IF ( CHILD.SLUG = VAL , CHILD.SLUG , IF ( ORIG.SLUG = VAL , ORIG.SLUG, [subquery] ) ) ),
     *  with subquery = (select translated_table.slug
     *              FROM [original_table] AS translated_table
     *              WHERE translated_table.original_id = [original_table].id
     *              AND translated_table.language_id <> [original_table].language_id
     *              LIMIT 1
     *
     *      (info : the subquery is executed only if the original slug and the child slug not match with the value
     *      She is called only on default language, when the user come from another language,
     *      else translated_table.original_id = [original_table].id return always false )
     *
     *      => It allows to test the slug on the original and the custo in user to switch of langue and conserv
     *                                                                  the current page
     *      => So, this behavior allows developper to find an element from slug in all translation
     *
     * @param $matches
     * @return string
     */
    public function _checkWhereTranslation($matches){
        $columnName = $matches[2];

        if(isset($this->_replacementColumnAlreadyFetched[$columnName])){
            return $this->_replacementColumnAlreadyFetched[$columnName];
        }

        //If it is the slug name, we do another operation
        //(we not take the original if the child col is null, we test two value)
        if('slug' == $columnName
            && null !== $this->_value){

            $_value = trim($this->_value);

            //To support array of slug
            $operand = '=';
            if(('(' != $_value[0]
                && substr_count($this->_request, '(') >= 1
                && substr_count($this->_request, ')') >= 1
                && stripos($this->_request, 'in') < stripos($this->_request, '('))
                || strpos($this->_request, ' LIKE ') !== false){ //in a slug, must not have a witespace, so it is the operand LIKE
                                                         //and not a word in a sequence

                return $matches[0];
            }

            $_values = explode($operand, $this->_value);
            if(count($_values) > 1){ //To remove the first operand
                //warning, the column must be declare before the value (slug = "value" and not "value" = slug)
                array_shift($_values);
                $_values = implode($operand, $_values);
            }
            elseif(count($_values) == 1){
                $_values = current($_values);
            }
            else{
                $_values = '';
            }

            $a = '`'.$this->_getSelectTableName().'`.`'.$columnName.'`';
            $b = '`'.self::CHILD_PREFIX . $this->_getSelectTableName().'`.`'.$columnName.'`';

            Centurion_Db_Table_Abstract::setFiltersStatus(false);
            //Build the subrequest to execute if to previous test fail
            //Pass by adapter for the select is not intercepted by any trait
            $adapter = $this->_select->getAdapter();
            //(and we use another relation name for this correlation)
            $request = $adapter->select()
                ->from(array('translated_table' => $this->_getSelectTableName()))
                ->reset(Centurion_Db_Table_Select::COLUMNS)
                ->columns('slug')
                    //To get only other version of the original (work only with default language)
                ->where('original_id='.$this->_getSelectTableName().'.id')
                ->where('slug LIKE '.$_values) //it is already escaped
                ->where('language_id<>'.$this->_getSelectTableName().'.language_id')->limit(1)->assemble();

            Centurion_Db_Table_Abstract::restoreFiltersStatus();

            //$this->_value if already quote by filter
            return '( if('.$b.' '.$operand.' '.$_values
                            .' , '.$b.', if('.$a.' '.$operand.' '.$_values.', '.$a.', ('.$request.') ) ) )';
        }
        else{
            $this->_replacementColumnAlreadyFetched[$columnName] =
                '(ifnull(`'.self::CHILD_PREFIX . $this->_getSelectTableName().'`.`'.$columnName
                                                    .'`,`'.$this->_getSelectTableName().'`.`'.$columnName.'`))';
        }

        return $this->_replacementColumnAlreadyFetched[$columnName];
    }

    /**
     * Method to check all translatable column from a part of a query and convert them to ifnull(...) to support
     * localisation
     * @param string $condition
     * @param null|string $value
     * @return mixed
     */
    public function addSupportOfLocalisation($condition, $value=null){

        /* this is the regex we apply to the condition we get.
            0. nothing interesting
            1. leading opening parenthesis
            2. table name with quotes
            3. table name without quotes
            4. column name with quotes
            5. column name without quotes
        */
        if (Centurion_Db_Table_Abstract::getFiltersStatus()
            && $this->_tableImplementsTranslation()
            && null !== $this->_getSelectTableName()){

            //Store the value to allows _checkWhereTranslation to access it
            if(!empty($value)){
                $this->_value = $this->_adapter->quote($value);
                $this->_request = $condition;
            }
            else{
                $this->_value = $condition;
                $this->_request = $condition;
            }

            //Find all column, check if they are translatable, and if it's the case,
            //add a condition to test with also with the translated field
            $condition = preg_replace_callback(
                $this->returnWherePattern(),
                array($this, '_checkWhereTranslation'),
                $condition
            );

            $this->_request = null;
            $this->_value = null;
        }

        return $condition;
    }

    /**
     * build a where clause in case of translation
     *
     * should be protected, but due to trait implementation, it cannot be
     *
     * @see Ceturion_Db_Table_Select::_where
     * @access public
     **/
    public function _where($condition, $value = null, $type = null, $bool = true){
        if (count($this->_parts[Centurion_Db_Table_Select::UNION])) {
            require_once 'Zend/Db/Select/Exception.php';
            throw new Zend_Db_Select_Exception(
                    "Invalid use of where clause with ".Centurion_Db_Table_Select::SQL_UNION
                );
        }

        $condition = $this->addSupportOfLocalisation($condition, $value);

        if ($value !== null) {
            $condition = $this->_adapter->quoteInto($condition, $value, $type);
        }

        $cond = '';
        if ($this->_parts[Centurion_Db_Table_Select::WHERE]) {
            if ($bool === true) {
                $cond = Centurion_Db_Table_Select::SQL_AND . ' ';
            } else {
                $cond = Centurion_Db_Table_Select::SQL_OR . ' ';
            }
        }

        return $cond . "( $condition )";
    }

    /**
     * Method to return the where pattern to extract column
     * @return null|string
     */
    public function returnWherePattern(){
        //Retrieve list of relations used here (the main table of the select and its relation for translation
        $relationsRegex = implode('|', array(
                    $this->_getSelectTableName(),
                    self::CHILD_PREFIX.$this->_getSelectTableName()
                )
            );

        $_translatableColumnList = $this->_getTransaltionSpec();
        $columnRegex = '\b'.implode('\b|\b', $_translatableColumnList).'\b';

        $quoteIdentifier = $this->_adapter->getQuoteIdentifierSymbol();
        //Build the pattern with the list of relations
        return '#(?:' . $quoteIdentifier . '?('.$relationsRegex.')'.$quoteIdentifier . '?\.)?'
                                       . $quoteIdentifier . '?('.$columnRegex.')' . $quoteIdentifier . '?#S';
    }
}
