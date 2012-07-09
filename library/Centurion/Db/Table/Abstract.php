<?php
/**
 * Centurion
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@centurion-project.org so we can send you a copy immediately.
 *
 * @category    Centurion
 * @package     Centurion_Db
 * @subpackage  Table
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Db
 * @subpackage  Table
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Nicolas Duteil <nd@octaveoctave.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 * @author      Antoine Roesslinger <ar@octaveoctave.com>
 */
abstract class Centurion_Db_Table_Abstract extends Zend_Db_Table_Abstract implements Countable, Centurion_Traits_Traitsable
{
    const CREATED_AT_COL = 'created_at';
    const UPDATED_AT_COL = 'updated_at';
    const RETRIEVE_ROW_ON_INSERT = 'retrieve';
    const VERBOSE = 'verbose';
    const MANY_DEPENDENT_TABLES = 'manyDependentTables';

    const TABLE_IS_TESTABLE = 'tableTestable';
    const ROW_IS_TESTABLE = 'rowTestable';
    
    const FILTERS_ON  = true;
    const FILTERS_OFF = false;

    /**
     * Many to Many dependent tables.
     *
     * @var array
     */
    protected $_manyDependentTables = array();

    /**
     * Classname for row.
     *
     * @var string
     */
    protected $_rowClass = 'Centurion_Db_Table_Row';

    /**
     * Classname for rowset.
     *
     * @var string
     */
    protected $_rowsetClass = 'Centurion_Db_Table_Rowset';

    protected static $_kinds = array(
        'day'   => '%Y-%m-%d',
        'month' => '%Y-%m-01',
        'year'  => '%Y-01-01',
    );

    protected $_cache = null;

    protected $_meta = null;

    protected $_selectClass = 'Centurion_Db_Table_Select';

    protected $_select = null;

    protected $_config = array();

    /**
     * Default options for cache backend defined in config ('resources.cachemanager.class')
     * Setted by the main bootstrap in /application
     * @var array
     */
    protected static $_defaultBackendOptions = array();

    /**
     * Default options for cache frontent defined in config ('resources.cachemanager.class')
     * Setted by the main bootstrap in /application
     * @var array
     */
    protected static $_defaultFrontendOptions = array();

    protected $_traitQueue;

    private static $_filtersOn = self::FILTERS_ON;
    private static $_previousFiltersStatus = array(self::FILTERS_ON);

    public static function getFiltersStatus()
    {
        return self::$_filtersOn;
    }

    public static function setFiltersStatus($status)
    {
        self::saveFiltersStatus();
        self::$_filtersOn = (bool) $status;
    }

    public static function saveFiltersStatus()
    {
        self::$_previousFiltersStatus[] = self::$_filtersOn;

    }

    public static function restoreFiltersStatus()
    {
        if(count(self::$_previousFiltersStatus)){
            self::$_filtersOn = array_pop(self::$_previousFiltersStatus);
        } else{
            throw new Exception('Error, there are no previous status in the stack');
        }
    }

    public static function switchFiltersStatus()
    {
        self::setFiltersStatus(!self::getFiltersStatus());
    }

    public function getTraitQueue()
    {
        if (null == $this->_traitQueue)
            $this->_traitQueue = new Centurion_Traits_Queue();

        return $this->_traitQueue;
    }

    public function __construct($config = array())
    {
        if (null === $this->_name) {
            $this->_name = Centurion_Inflector::tableize(str_replace('_Model_DbTable_', '', get_class($this)));
        }

        Centurion_Signal::factory('pre_init')->send($this);
        parent::__construct($config);

        $this->_config = $config;

        if (null === $this->_meta) {
            $this->_setupMeta();
        }

        Centurion_Traits_Common::initTraits($this);

        Centurion_Signal::factory('post_init')->send($this);
    }

    public function isAllowedContext($context, $resource = null)
    {
        return in_array($context, iterator_to_array($this->_traitQueue), true);
    }

    /**
     * Check if the current table have the given column
     * @param string $columnName
     * @return boolean true if current table have the column
     */
    public function hasColumn($columnName)
    {
        return in_array($columnName, $this->info(self::COLS));
    }
    
    /**
     * Check if the column is an index in the table
     * @param string $columnName
     * @return boolean true if current table have the column
     */
    public function isIndex($columnName)
    {
        if ($this->getAdapter() instanceof Zend_Db_Adapter_Pdo_Mysql) {
            foreach ($this->getAdapter()->query('show KEYS from ' . $this->_name)->fetchAll() as $row) {
                if (0 === strcmp($row['Column_name'], $columnName)) {
                    return true;
                }
            }
            
            return false;
        } else {
            throw new Centurion_Db_Table_Exception('Adapter is not MYSQL, so I can not check index.');
        }
    }

    /**
     * Find the column and table as set in foreign key of a column
     * @param string $columnName The name of the column to find foreign key
     * @return array|bool false if no foreign key, else array: array('table' => 'tablename', 'column' => 'column')
     * @throws Centurion_Db_Table_Exception
     */
    public function getMysqlForeignKey($columnName)
    {
        if ($this->getAdapter() instanceof Zend_Db_Adapter_Pdo_Mysql) {

            $createTable = $this->getAdapter()->query('show create table ' . $this->_name)->fetch();

            if (!isset($createTable['Create Table'])) {
                return false;
            }
            $sql = $createTable['Create Table'];

            if (preg_match('^CONSTRAINT .* FOREIGN KEY \(`'.$columnName.'`\) REFERENCES `(.*)\` \(`(.*)`\)^', $sql, $matches)) {
                return array('table' => $matches['1'], 'column' => $matches['2']);
            }
            return false;
        } else {
            throw new Centurion_Db_Table_Exception('Adapter is not MYSQL, so I can not check index.');
        }
    }

    public function delegateGet($context, $column)
    {
        if (!$this->isAllowedContext($context, $column))
            throw new Centurion_Db_Exception(sprintf('Unauthorized property %s', $column));

        return $this->{$column};
    }

    public function delegateSet($context, $column, $value)
    {
        if (!$this->isAllowedContext($context, $column))
            throw new Centurion_Db_Exception(sprintf('Unauthorized property %s', $column));

        $this->$column = $value;
    }

    public function delegateCall($context, $method, $args = array())
    {
        if (!$this->isAllowedContext($context, $method))
            throw new Centurion_Db_Exception(sprintf('Unauthorized method %s', $method));

        return call_user_func_array(array($this, $method), $args);
    }

    public function __get($property)
    {
        $trait = Centurion_Traits_Common::checkTraitPropertyExists($this, $property);
        if (null !== $trait) {
            return $trait->{$property};
        }
    }

    public function __set($column, $value)
    {
        return;
    }

    public function __isset($column)
    {
        if (Centurion_Traits_Common::checkTraitPropertyExists($this, $column)) {
            return true;
        }
        return false;
    }

    public function __unset($column)
    {
        return;
    }

    /**
     * Sleep function. We only save the config needed to make the table again.
     */
    public function __sleep()
    {
        return array('_config');
    }

    /**
     * Wake up after putting in cache.
     *
     * @todo think multiple database
     * @return void
     */
    public function __wakeup()
    {
        if ($this->_config) {
            $this->setOptions($this->_config);
        }

        $this->_setup();
        $this->init();

        if (null === $this->_meta) {
            $this->_setupMeta();
        }
    }

    /**
     * Fetches all rows.
     *
     * Honors the Zend_Db_Adapter fetch mode.
     *
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    public function all($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($where, $order, $count, $offset);
    }

    /**
     * Returns an instance of a Centurion_Db_Table_Select object.
     *
     * @param bool $withFromPart Whether or not to include the from part of the select based on the table
     * @return Centurion_Db_Table_Select
     */
    public function select($withFromPart = self::SELECT_WITHOUT_FROM_PART, $applyDefaultFilters = null, $stored = false)
    {
        if (!$stored || null === $this->_select) {
            $select = new $this->_selectClass($this);
            if ($withFromPart == self::SELECT_WITH_FROM_PART) {
                $select->from($this->info(self::NAME), Zend_Db_Table_Select::SQL_WILDCARD, $this->info(self::SCHEMA));
            } else {
                if (null === $applyDefaultFilters)
                    $applyDefaultFilters = self::FILTERS_OFF;
            }

            if (null === $applyDefaultFilters)
                $applyDefaultFilters = self::$_filtersOn;

            Centurion_Signal::factory('on_dbTable_select')->send($this, array($select, $applyDefaultFilters));

            if (!$stored) {
                return $select;
            }

            $this->_select = $select;
        }

        return $this->_select;
    }

    public function getSelectClass()
    {
        return $this->_selectClass;
    }


    /**
     * may be override to provide a way to get a filtered select
     * @return Centurion_Db_Table_Select
     */
    public function preFilteredSelect()
    {
        return $this->select(true);
    }    
    
    /**
     * Retrieve information about attached table.
     *
     * @param string $key OPTIONAL Key
     */
    public function info($key = null)
    {
        $this->_setupPrimaryKey();
        
        if (null !== $key) {
            switch ($key) {
                case self::NAME:
                    return $this->_name;
                case self::COLS:
                    return $this->_getCols();
                case self::MANY_DEPENDENT_TABLES:
                    return $this->_manyDependentTables;
                case self::SCHEMA:
                    return $this->_schema;
                case self::PRIMARY:
                    $this->_setupPrimaryKey();
                    return (array) $this->_primary;
                case self::METADATA:
                    return $this->_metadata;
                case self::ROW_CLASS:
                    return $this->getRowClass();
                case self::ROWSET_CLASS:
                    return $this->getRowsetClass();
                case self::REFERENCE_MAP:
                    return $this->_referenceMap;
                case self::DEPENDENT_TABLES:
                    return $this->_dependentTables;
                case self::SEQUENCE:
                    return $this->_sequence;
            }
        }

        return parent::info($key);
    }

    /**
     * Insert a new row.
     *
     * @param   array   $data   Column-value pairs
     * @return  mixed           The primary key of the row inserted
     */
    public function insert(array $data)
    {
        Centurion_Signal::factory('pre_insert')->send($this, $data);

        if (in_array(self::CREATED_AT_COL, $this->_getCols()) && empty($data[self::CREATED_AT_COL])) {
            $data[self::CREATED_AT_COL] = Zend_Date::now()->toString(Centurion_Date::MYSQL_DATETIME);
        }

        if (in_array(self::UPDATED_AT_COL, $this->_getCols()) && empty($data[self::UPDATED_AT_COL])) {
            $data[self::UPDATED_AT_COL] = Zend_Date::now()->toString(Centurion_Date::MYSQL_DATETIME);
        }

        $retrieveRowOnInsert = false;
        if (array_key_exists(self::RETRIEVE_ROW_ON_INSERT, $data)
            && $data[self::RETRIEVE_ROW_ON_INSERT] === true) {

            $retrieveRowOnInsert = true;
            unset($data[self::RETRIEVE_ROW_ON_INSERT]);
        }

        if (array_key_exists(self::VERBOSE, $data) && $data[self::VERBOSE] === false) {
            $data = array_intersect_key($data, array_flip($this->info('cols')));
        }

        $result = parent::insert($data);
        if ($retrieveRowOnInsert && null !== $result) {
                $primaryValues = array();

                if (is_string($result)) {
                    $primaryValues = array($result);
                } else {
                    foreach($this->_primary as $primary) {
                        $primaryValues[] = $result[$primary];
                    }
                }

                $result = call_user_func_array(array($this, 'find'), $primaryValues)->current();
        }

        Centurion_Signal::factory('post_insert')->send($this, $result);

        return $result;
    }

    /**
     * Updates existing rows.
     *
     * @param  array        $data  Column-value pairs
     * @param  array|string $where An SQL WHERE clause, or an array of SQL WHERE clauses
     * @return int                 The number of rows updated
     */
    public function update(array $data, $where)
    {
        Centurion_Signal::factory('pre_update')->send($this, array($data, $where));

        if (in_array(self::UPDATED_AT_COL, $this->_getCols()) && empty($data[self::UPDATED_AT_COL])) {
            $data[self::UPDATED_AT_COL] = Zend_Date::now()->toString(Centurion_Date::MYSQL_DATETIME);
        }

        $count = parent::update($data, $where);
        Centurion_Signal::factory('post_update')->send($this, array($data, $where, $count));

        return $count;
    }

    /**
     * Smart save.
     *
     * @param   array   $data   Associative array with row data
     * @return  mixed           Primary key value
     */
    public function save($data)
    {
        $this->_setupPrimaryKey();
        $cols = array_intersect_key($data, array_flip($this->_getCols()));

        if (array_intersect((array) $this->_primary, array_keys(array_filter($cols)))) {
            if (is_array($this->_primary)) {
                $a = array();

                foreach ($this->_primary as $pk) {
                    $a[] = $cols[$pk];
                }

                if (count($this->_primary) != count($a)) {
                    throw new Centurion_Db_Table_Exception('Invalid primary key.(Primary key is composed, but incomplete)');
                }

                $rows = call_user_func_array(array($this , 'find'), $a);
            } else {
                $rows = $this->find($cols[$this->_primary]);
            }
            if (1 == $rows->count()) {
                $pk = $rows->current()->setFromArray($cols)->save();
            } elseif (0 == $rows->count()) {
                $pk = $this->insert($cols);
            } else {
                throw new Centurion_Db_Table_Exception('Error updating requested row.(More than 1 row or invalid Id?!)');
            }
        } else {
            $pk = $this->insert($v = array_diff_key($cols, array_flip((array) $this->_primary)));
        }

        return $pk;
    }

    /**
     * Adds support for magic finders, inspired by Doctrine_Table.
     *
     * This method add support for calling methods not defined in code, such as:
     * findByColumnName, findByRelationAlias
     * findOneByColumnName, findOneByNotColumnName
     * findById, findByContactId, etc.
     *
     * @return Centurion_Db_Table_Row|Centurion_Db_Table_Rowset The result of the finder
     */
    public function __call($method, array $args)
    {
        $lcMethod = strtolower($method);

        /**
         * @deprecated : to much time consuming wihtout real gain. use $this->findOneBy('id', 1) instead of $this->findOneById(1); Preserve also autocompletion.
         */
        if (substr($lcMethod, 0, 6) == 'findby') {
            $by = substr($method, 6, strlen($method));
            $method = '_findBy';
        } else if (substr($lcMethod, 0, 9) == 'findoneby') {
            $by = substr($method, 9, strlen($method));
            $method = '_findOneBy';
        }

        if (isset($by)) {
            if (!isset($args[0])) {
                throw new Centurion_Db_Table_Exception('You must specify the value to ' . $method);
            }

            return $this->{$method}($by, $args);
        }

        list($found, $retVal) = Centurion_Traits_Common::checkTraitOverload($this, $method, $args);

        if ($found) {
            return $retVal;
        }

        throw new Centurion_Db_Table_Exception(sprintf("method %s does not exist", $method));
    }

    /**
     * Generates a string representation of this object, inspired by Doctrine_Table.
     *
     * @TODO: this method should be refactored
     * @return Centurion_Db_Table_Select
     */
    protected function _buildFindByWhere($by, $values)
    {
        $values = (array) $values;
        $ands = array();
        $e = explode('And', $by);
        $i = 0;
        foreach ($e as $k => $v) {
            $and = '';
            $e2 = explode('Or', $v);
            $ors = array();
            foreach ($e2 as $k2 => $v2) {
                $v2 = Centurion_Inflector::tableize($v2);
                $fieldName = $this->_isFieldNameBelongToTable($v2);
                if ($fieldName) {
                    if (substr($values[$i], 0, 1) == '!') {
                        $ors[] = $this->getAdapter()->quoteInto(sprintf('%s.%s != ?', $this->_name, $this->getAdapter()->quoteIdentifier($fieldName)), substr($values[$i], 1));
                    } else {
                        $ors[] = $this->getAdapter()->quoteInto(sprintf('%s.%s = ?', $this->_name, $this->getAdapter()->quoteIdentifier($fieldName)), $values[$i]);
                    }
                    $i++;
                } else {
                    throw new Centurion_Db_Table_Exception(str_replace('{__FIELDNAME__}', $v2, Centurion_Db_Table_Exception::FIELDNAME_NOT_BELONG));
                }
            }
            $and .= implode(' OR ', $ors);
            $and = count($ors) > 1 ? '(' . $and . ')':$and;
            $ands[] = $and;
        }
        $where = implode(' AND ', $ands);

        return $this->select()
                    ->where($where);
    }

    /**
     * Find a reference with a column name.
     *
     * @param   string          $columnName Column name to search
     * @return  array|boolean   The reference map entry
     */
    public function getReferenceByColumnName($columnName)
    {
        $references = $this->_getReferenceMapNormalized();
        foreach ($references as $key => $value) {
            if (!in_array($columnName, $value[self::COLUMNS])) {
                continue;
            }

            return $value[self::REF_TABLE_CLASS];
        }

        return false;
    }

    /**
     * Retrieve a referenceMap entry with its key name or all referenceMaps if not key is given.
     *
     * @param string $name Key name
     * @return array
     * @throws Centurion_Db_Exception When the referenceMap key does not exist
     */
    public function getReferenceMap($name = null, $throwException = true)
    {
        if (null !== $name) {
            if (!array_key_exists($name, $this->_referenceMap)) {
                if (true === $throwException) {
                    throw new Centurion_Db_Exception(sprintf('referenceMap key "%s" does not exist', $name));
                } else {
                    return false;
                }
            }

            return $this->_referenceMap[$name];
        }

        return $this->_referenceMap;
    }

    /**
     *
     * @param string   $cond  The WHERE condition.
     * @param mixed    $value OPTIONAL The value to quote into the condition.
     * @param constant $type  OPTIONAL The type of the given value
     * @return string
     */
    public function rowsCount($condition = null, $value = null, $type = null)
    {
        if ($condition instanceof Centurion_Db_Table_Select) {
            $select = $condition;
        } else {
            $select = $this->select()
                           ->from($this->_name, 'COUNT(*)');
            if (null !== $condition)
                $select->where($condition, $value, $type);
        }

        return $this->getAdapter()->fetchOne($select);
    }

    /**
     * Deletes existing rows.
     *
     * @param  array|string $where SQL WHERE clause(s).
     * @return int          The number of rows deleted.
     */
    public function delete($where)
    {
        Centurion_Signal::factory('pre_delete')->send($this, array($where));

        list($found, $return) = Centurion_Traits_Common::checkTraitOverload($this, 'delete', array($where));

        if (!$found) {
            $return = parent::delete($where);
        }

        Centurion_Signal::factory('post_delete')->send($this, array($where));
        return $return;
    }

    public function getCacheTag()
    {
        return sprintf('__%s', $this->info(Centurion_Db_Table_Abstract::NAME));
    }
    
    /**
     * Deletes existing rows.
     *
     * @param  array|string $where SQL WHERE clause(s).
     * @return int          The number of rows deleted.
     */
    public function deleteRow($where)
    {
        $rowSet = $this->_where($this->select(true), $where)->fetchAll();
        $return = true;
        foreach ($rowSet as $row) {
            $return &= $row->delete();
        }

        return $return;
    }

    /**
     * Proxy method for rowsCount
     *
     * @param string   $cond  The WHERE condition.
     * @param mixed    $value OPTIONAL The value to quote into the condition.
     * @param constant $type  OPTIONAL The type of the given value
     * @return string
     */
    public function count($condition = null, $value = null, $type = null)
    {
        return $this->rowsCount($condition, $value, $type);
    }

    /**
     *  Returns a list of date objects with their count representing all available dates for
     *  the given fieldName, scoped to 'kind'.
     *
     * @param string $fieldName
     * @param string $kind
     * @param string $order
     * @return void
     */
    public function dates($fieldName, $kind, $order = 'ASC')
    {
        if (!in_array($kind, array_keys(self::$_kinds))) {
            throw new Centurion_Db_Exception("'kind' must be one of 'year', 'month' or 'day'.");
        }

        if (!in_array($order, array('ASC', 'DESC'))) {
            throw new Centurion_Db_Exception("'order' must be either 'ASC' or 'DESC'.");
        }

        return $this->select()
                ->distinct()
                ->from(array('p' => $this->info('name')),
                       array(
                           'date'     => new Zend_Db_Expr("CAST(DATE_FORMAT(" . $fieldName . ", '" . self::$_kinds[$kind] . " 00:00:00') AS DATETIME)"),
                           'count'    => new Zend_Db_Expr("COUNT(*)")))
                ->group('date')
                ->order(sprintf('%s %s', $fieldName, $order));
    }


    /**
     *  Looks up an object with the given kwargs, creating one if necessary.
     *  Returns a tuple of (object, created), where created is a boolean
     *  specifying whether an object was created.
     *
     * @param array $kwargs
     * @return array
     */
    public function getOrCreate(array $kwargs)
    {
        try {
            return array($this->get($kwargs), false);
        } catch (Centurion_Db_Table_Row_Exception_DoesNotExist $e) {
            $row = $this->createRow($kwargs);
            $row->save();

            return array($row, true);
        }
    }


    /**
     *  Looks up an object with the given kwargs, creating one if necessary.
     *  Returns a true if created, false if already exist.
     *
     * @param array $kwargs
     * @todo use insert ignore (or alternative) when SGBD is compatible
     * @return bool
     */
    public function insertIfNotExist(array $kwargs)
    {
        list(, $created) = $this->getOrCreate($kwargs);
        return $created;
    }

    /**
     *  Performs the query and returns a single object matching the given
     *  keyword arguments.
     *
     * @param array $kwargs
     * @todo raise an exception when two result set is returned
     * @return void
     */
    public function get(array $kwargs)
    {
        $object = $this->filter($kwargs);

        $num = $object->count();

        if ($num === 1) {
            return $object->current();
        }

        if (!$num) {
            throw new Centurion_Db_Table_Row_Exception_DoesNotExist(sprintf("%s matching query does not exist.", get_class($this)));
        }

        throw new Centurion_Db_Table_Row_Exception_MultipleObjectsReturned(sprintf("get() returned more than one %s -- it returned %s! Lookup parameters were %s",
                                                                           get_class($this), $num, json_encode($kwargs)));
    }

    /**
     *  Returns a new Zend_Db_Select instance with the args ANDed to the existing
     *  set.
     *  If the first character of the column name is '!', it will set a WHERE NOT clause.
     *  If the value is null, it will set a WHERE IS NULL clause.
     *
     * @param array $kwargs
     * @todo add new fonctionalities, see django.db.models.query
     * @return void
     */
    public function filter(array $kwargs, $select = null)
    {
        if (null === $select) {
            $select = $this->select(true);
        }

        return $select->filter($kwargs)->fetchAll();
    }

    /**
     * Called by parent table's class during delete() method.
     *
     * @param  string $parentTableClassname
     * @param  array  $primaryKey
     * @return int    Number of affected rows
     */
    public function _cascadeDelete($parentTableClassname, array $primaryKey)
    {
        $this->_setupMetadata();
        $rowsAffected = 0;

        foreach ($this->_getReferenceMapNormalized() as $map) {
            if ($map[self::REF_TABLE_CLASS] == $parentTableClassname && isset($map[self::ON_DELETE])) {
                switch ($map[self::ON_DELETE]) {
                    case self::CASCADE:
                        $where = array();
                        for ($i = 0, $count = count($map[self::COLUMNS]); $i < $count; ++$i) {
                            $col = $this->_db->foldCase($map[self::COLUMNS][$i]);
                            $refCol = $this->_db->foldCase($map[self::REF_COLUMNS][$i]);

                            $type = $this->_metadata[$col]['DATA_TYPE'];
                            $where[] = $this->_db->quoteInto(
                                    sprintf('%s.%s = ?', $this->_db->quoteIdentifier($this->_name), $this->_db->quoteIdentifier($col, true)),
                                    $primaryKey[$refCol], $type
                            );
                        }

                        /*
                        * Fix : Suround, in the implode, AND with withspaces because if the relation is build with several key            
                        * the implode returned : "myKey=XANDmySecondKey=Y" instead of "myKey=X AND mySecondKey=Y"
                        */
                        foreach ($this->fetchAll(implode(' AND ', $where)) as $row) {
                            $rowsAffected += $row->delete();
                        }

                        // old way but it's not trapped by the row, and if you have routines in your delete method of your row...
                        // $rowsAffected += $this->delete($where);
                        break;

                    case self::SET_NULL:
                        for ($i = 0, $count = count($map[self::COLUMNS]); $i < $count; ++$i) {
                            $col = $this->_db->foldCase($map[self::COLUMNS][$i]);
                            $refCol = $this->_db->foldCase($map[self::REF_COLUMNS][$i]);
                            $type = $this->_metadata[$col]['DATA_TYPE'];
                            $where = $this->_db->quoteInto(
                                sprintf('%s.%s = ?', $this->_db->quoteIdentifier($this->_name), $this->_db->quoteIdentifier($col, true)),
                                $primaryKey[$refCol], $type);

                            $rowsAffected += $this->update(array($col => null), $where);
                        }
                        break;
                    default:
                        // no action
                        break;
                }
            }
        }

        return $rowsAffected;
    }

    /**
     * Set meta information.
     *
     * @param array $values Values
     * @return Centurion_Db_Table_Abstract
     */
    public function setMeta(array $values)
    {
        $this->_meta = $values;

        return $this;
    }

    /**
     * Get meta information.
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->_meta;
    }

    /**
     * Get Many dependent tables.
     *
     * @return array
     */
    public function getManyDependentTables()
    {
        return $this->_manyDependentTables;
    }

    /**
     * Get a random row.
     *
     * @param Zend_Db_Table_Select|array $select The select used to process the query
     * @return Centurion_Db_Table_Row_Abstract
     */
    public function random($where = null)
    {

        if (!($where instanceof Zend_Db_Select)) {
            $select = $this->select(true);
            $select = $this->_where($select, $where);
        } else {
            $select = $where;
            $where = null;
        }

        $select = $select->order(new Zend_Db_Expr('RAND()'));

        return $select->fetchRow();
    }

    public function getCountsSelect ($groupby, $filter = null, $order = null, $limit = null)
    {
        if ($filter instanceof Centurion_Db_Table_Select) {
            $select = $filter;
            $filter = array();
        } else {
            $select = $this->select(true);
            $filter = (array) $filter;
        }

        if (!in_array($groupby, $this->info(Centurion_Db_Table_Abstract::COLS)) && !$select->hasColumn($groupby)) {
            $select->setIntegrityCheck(false);
            $col = new Zend_Db_Expr($select->addRelated($groupby));
            $select->columns($col);
        } else {
            $col = $groupby;
        }

        $select->group($col);

        $select->columns(array('_nb' => new Zend_Db_Expr('COUNT(*)')));

        if (null !== $filter)
            $select->filter($filter);

        if ($limit)
            $select->limit($limit);

        if ($order)
            $select->order($order);

//            $select->order(new Zend_Db_Expr('_nb DESC'));

        return $select;
    }

    public function getCounts($groupby, $filter = null, $order = null, $limit = null)
    {
        $select = $this->getCountsSelect($groupby, $filter, $order, $limit);

        return $select->count();
    }

    /**
     * Get the cached table.
     *
     * @param string $frontendOptions
     * @param string $backendOptions
     * @param string $backendName
     * @return Centurion_Db_Cache
     */
    public function getCache($frontendOptions = null, $backendOptions = null, $backendName = null)
    {
        if (null === $this->_cache) {
            if (null === $frontendOptions) {
                $frontendOptions = self::$_defaultFrontendOptions;
            }

            if (null === $backendOptions) {
                $backendOptions = self::$_defaultBackendOptions;
            }

            $this->_cache = new Centurion_Db_Cache($this, $frontendOptions, $backendOptions, $backendName);
        }

        return $this->_cache;
    }

    /**
     * Retrieve the default backend options for all tables.
     *
     * @return array
     */
    public static function getDefaultBackendOptions()
    {
        return self::$_defaultBackendOptions;
    }

    /**
     * Retrieve the default frontend options for all tables.
     *
     * @return array
     */
    public static function getDefaultFrontendOptions()
    {
        return self::$_defaultFrontendOptions;
    }

    /**
     * Set the default backend options for all tables.
     *
     * @param array $options
     * @return void
     */
    public static function setDefaultBackendOptions(array $options = array())
    {
        self::$_defaultBackendOptions = $options;
    }

    /**
     * Set the default frontend options for all tables.
     *
     * @param array $options
     * @return void
     */
    public static function setDefaultFrontendOptions(array $options = array())
    {
        self::$_defaultFrontendOptions = $options;
    }

    /**
     * Setup meta information.
     *
     * @return Centurion_Db_Table_Abstract
     */
    protected function _setupMeta()
    {
        $name = explode('_', $this->_name);
        $verboseName = implode('_', array_splice($name, 1));
        $this->setMeta(array(
            'verboseName'  =>  $verboseName,
            'verbosePlural'=>  sprintf('%s_set', $verboseName)
        ));

        return $this;
    }


    /**
     * @deprecated use public function findOneBy instead
     */
    protected function _findOneBy($fieldName, $values)
    {
        return $this->findOneBy($fieldName, $values);
    }

    /**
     * Find a row with a formatted field name.
     *
     * @param   string    $fieldName    Formatted field name
     * @param   array     $values       Values
     */
    public function findOneBy($fieldName, $values)
    {
        return $this->fetchRow($this->_buildFindByWhere($fieldName, $values));
    }

    /**
     * Find a rowset with a formatted field name.
     *
     * @param   string    $fieldName    Formatted field name
     * @param   array     $values       Values
     */
    public function findBy($fieldName, $values)
    {
        return $this->fetchAll($this->_buildFindByWhere($fieldName, $values));
    }

    /**
     * @deprecated use public function findBy instead
     */
    protected function _findBy($fieldName, $values)
    {
        return $this->findBy($fieldName, $values);
    }

    /**
     * Check if a field name belongs to the table.
     *
     * @param   string          $fieldName
     * @return  boolean|string  False if the field doesn't belong to the table, otherwise, tableized field
     */
    protected function _isFieldNameBelongToTable($fieldName)
    {
        $fieldName = Centurion_Inflector::tableize($fieldName);
        if (in_array($fieldName, $this->_getCols()))
            return $fieldName;

        return false;
    }

    /**
     * Proxy function to test _genRefRuleName
     * @see Centurion_Db_Table_TableTest
     * @param string $base
     * @return string
     */
    public function testGenRefRuleName($base)
    {
        return $this->_genRefRuleName($base);
    }
    /**
     * generate a new name for a reference rule (guarantee name uniqness)
     * @param string $base desired name for the rule if it is already taken a suffix will be added
     */
    protected function _genRefRuleName($base)
    {
        if ('' == trim($base))
            $base = uniqid();

        $refMapRule = $base;
        $i = 1;
        
        $existingRefRules = array();
        $mergeAllRefs = array_merge($this->getReferenceMap(), $this->getDependentTables(), $this->getManyDependentTables());
        
        foreach ($mergeAllRefs as $key => $val) {
             if (!is_int($key)) {
                 $existingRefRules[$key] = true;
             }
        }
        
        while (isset($existingRefRules[$refMapRule])) {
            $refMapRule = sprintf('%s_%u', $base, $i);
            $i++;
        }

        return $refMapRule;
    }
    
    public function getTestCondition()
    {
        return null;
    }
}
