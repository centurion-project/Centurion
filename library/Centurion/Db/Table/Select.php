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
 * @author      Mathias Desloges <m.desloges@gmail.com>
 * @todo        refactor filter method, add more unit tests
 * @TODO        Make a contain function that check if a row is in a select
 */
class Centurion_Db_Table_Select extends Zend_Db_Table_Select implements Countable
{
    const RULES_SEPARATOR = '__';

    const JOIN_TYPE_SEPARATOR = '|';
    const JOIN_TYPE_LEFT = 'left';
    const JOIN_TYPE_INNER = 'inner';
    const JOIN_TYPE_OUTER = 'outer';

    const OPERATOR_GREATER = 'gt';
    const OPERATOR_LESS = 'lt';
    const OPERATOR_GREATER_EQUAL = 'gte';
    const OPERATOR_LESS_EQUAL = 'lte';
    const OPERATOR_EXACT = 'exact';
    const OPERATOR_CONTAINS = 'contains';
    const OPERATOR_IN = 'in';
    const OPERATOR_ISNULL = 'isnull';
    const OPERATOR_RANGE = 'range';
    const OPERATOR_YEAR = 'year';
    const OPERATOR_MONTH = 'month';
    const OPERATOR_DAY = 'day';
    const OPERATOR_NEGATION = '!';
    const OPERATOR_OR = '~';

    const HYDRATE_SEPARATOR = '___';

    const DEFAULT_OPERATOR = self::OPERATOR_EXACT;

    const ROW_COUNT_COLUMN = 'centurion_db_table_select_row_count';

    protected static $_operators = array(
        self::OPERATOR_GREATER          =>  array('<=', '>', null),
        self::OPERATOR_GREATER_EQUAL    =>  array('<', '>=', null),
        self::OPERATOR_LESS             =>  array('>=', '<', null),
        self::OPERATOR_LESS_EQUAL       =>  array('>', '<=', null),
        self::OPERATOR_CONTAINS         =>  array('NOT LIKE', 'LIKE', null),
        self::OPERATOR_IN               =>  array('NOT IN', 'IN', '(%s)'),
        self::OPERATOR_ISNULL           =>  array('IS NOT', 'IS', 'NULL'),
        self::OPERATOR_RANGE            =>  array('NOT BETWEEN', 'BETWEEN', '%s AND %s'),
        self::OPERATOR_EXACT            =>  array('!=', '=', null),
        self::OPERATOR_YEAR             =>  array(null, null, 'EXTRACT(YEAR FROM %s)'),
        self::OPERATOR_MONTH            =>  array(null, null, 'EXTRACT(MONTH FROM %s)'),
        self::OPERATOR_DAY              =>  array(null, null, 'EXTRACT(DAY FROM %s)')
    );

    protected static $_joinMethod = array(self::JOIN_TYPE_INNER => 'join',
                                          self::JOIN_TYPE_LEFT  => 'joinLeft',
                                          self::JOIN_TYPE_OUTER => '');

    protected static $_joinTypeMap = array(self::JOIN_TYPE_INNER => self::INNER_JOIN,
                                           self::JOIN_TYPE_LEFT  => self::LEFT_JOIN,
                                           self::JOIN_TYPE_OUTER => '');

    protected static $_suffixes = array(self::OPERATOR_GREATER, self::OPERATOR_LESS, self::OPERATOR_GREATER_EQUAL,
                                        self::OPERATOR_LESS_EQUAL, self::OPERATOR_EXACT, self::OPERATOR_CONTAINS,
                                        self::OPERATOR_IN, self::OPERATOR_ISNULL, self::OPERATOR_RANGE,
                                        self::OPERATOR_YEAR, self::OPERATOR_MONTH, self::OPERATOR_DAY);

    protected static $_prefixes = array(self::OPERATOR_NEGATION, self::OPERATOR_OR);

    /**
     * Clone the current query and do a "count(1)" query in order to get the number of element that the original query will return.
     *
     * <code>
     * <?php
     * $nb = $select->count();
     * ?>
     * </code>
     *
     * If you will fetch all row, use instead this code to save one query:
     * <code>
     * <?php
     * $rowSet = $select->fetchAll();
     * $nb = count($rowSet);
     * ?>
     * </code>
     *
     * @see Zend_Paginator_Adapter_DbSelect::count()
     * @return int number of element of the selected query
     */
    public function count()
    {
        $rowCount = clone $this;
        $rowCount->__toString(); // Workaround for ZF-3719 and related

        $db = $rowCount->getAdapter();

        $countColumn = $db->quoteIdentifier($db->foldCase(self::ROW_COUNT_COLUMN));
        $countPart   = 'COUNT(1) AS ';
        $groupPart   = null;
        $unionParts  = $rowCount->getPart(Zend_Db_Select::UNION);

        /**
         * If we're dealing with a UNION query, execute the UNION as a subquery
         * to the COUNT query.
         */
        if (!empty($unionParts)) {
            $expression = new Zend_Db_Expr($countPart . $countColumn);

            $rowCount = $db->select()->from($rowCount, $expression);
        } else {
            $columnParts = $rowCount->getPart(Zend_Db_Select::COLUMNS);
            $groupParts  = $rowCount->getPart(Zend_Db_Select::GROUP);
            $havingParts = $rowCount->getPart(Zend_Db_Select::HAVING);
            $isDistinct  = $rowCount->getPart(Zend_Db_Select::DISTINCT);

            /**
             * If there is more than one column AND it's a DISTINCT query, more
             * than one group, or if the query has a HAVING clause, then take
             * the original query and use it as a subquery os the COUNT query.
             */
            if (($isDistinct && count($columnParts) > 1) || count($groupParts) > 1 || !empty($havingParts)) {
                $rowCount = $db->select()->from($this->_select);
            } else if ($isDistinct) {
                $part = $columnParts[0];

                if ($part[1] !== Zend_Db_Select::SQL_WILDCARD && !($part[1] instanceof Zend_Db_Expr)) {
                    $column = $db->quoteIdentifier($part[1], true);

                    if (!empty($part[0])) {
                        $column = $db->quoteIdentifier($part[0], true) . '.' . $column;
                    }

                    $groupPart = $column;
                }
            } else if (!empty($groupParts) && $groupParts[0] !== Zend_Db_Select::SQL_WILDCARD &&
                       !($groupParts[0] instanceof Zend_Db_Expr)) {
                $groupPart = $db->quoteIdentifier($groupParts[0], true);
            }

            /**
             * If the original query had a GROUP BY or a DISTINCT part and only
             * one column was specified, create a COUNT(DISTINCT ) query instead
             * of a regular COUNT query.
             */
            if (!empty($groupPart)) {
                $countPart = 'COUNT(DISTINCT ' . $groupPart . ') AS ';
            }

            /**
             * Create the COUNT part of the query
             */
            $expression = new Zend_Db_Expr($countPart . $countColumn);

            $rowCount->reset(Zend_Db_Select::COLUMNS)
                     ->reset(Zend_Db_Select::ORDER)
                     ->reset(Zend_Db_Select::LIMIT_OFFSET)
                     ->reset(Zend_Db_Select::GROUP)
                     ->reset(Zend_Db_Select::DISTINCT)
                     ->reset(Zend_Db_Select::HAVING)
                     ->columns($expression);
        }

        $columns = $rowCount->getPart(Zend_Db_Select::COLUMNS);

        $countColumnPart = $columns[0][1];

        if ($countColumnPart instanceof Zend_Db_Expr) {
            $countColumnPart = $countColumnPart->__toString();
        }

        $rowCountColumn = $this->getAdapter()->foldCase(self::ROW_COUNT_COLUMN);

        // The select query can contain only one column, which should be the row count column
        if (false === strpos($countColumnPart, $rowCountColumn)) {
                throw new Exception('Row count column not found');
        }

        $result = $rowCount->query(Zend_Db::FETCH_ASSOC)->fetch();
        return count($result) > 0 ? $result[$rowCountColumn] : 0;
    }

    /**
     * Check if the table has already been joined to avoid multiple join of the same table
     *
     * @param string $tableName
     * @param string $joinCond
     */
    protected function _isAlreadyJoined($tableName, $joinCond = null) {
        $fromParts = $this->getPart(self::FROM);
        
        try {
            
            foreach ($fromParts as $from) {
                if (strcmp($from['tableName'], $tableName) == 0) {
                    if (null == $joinCond) {
                        return true;
                    }

                    //TODO: this should be foreach
                    if ($this->_isConditionEquals($from['joinCondition'], $joinCond)) {
                        return true;
                    }
                }
            }
        } catch (Exception $e) {
            return false;
        }
        
        return false;
    }
    
    protected function _isConditionEquals($cond1, $cond2)
    {
        if (false !== strpos($cond1, '(') || false !== strpos($cond2, '(')) {
            throw new Exception('Not yet supported');
        }
        
        $cond1 = strtolower($cond1);
        $cond2 = strtolower($cond2);
        
        $tabAnd1 = explode(' and ', $cond1);
        $tabAnd2 = explode(' and ', $cond2);
        
        if (count($tabAnd1) != count($tabAnd2)) {
            return false;
        }
        
        $tabAnd1 = array_map(array($this, 'normalizeCondition'), $tabAnd1);
        $tabAnd2 = array_map(array($this, 'normalizeCondition'), $tabAnd2);
        
        foreach ($tabAnd1 as $val) {
            $found = false;
            foreach ($tabAnd2 as $val2) {
                if (strcmp($val, $val2) == 0) {
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                return false; 
            }
        }
        
        return true;
    }
    
    public function forcePrefix($cond) {
        
        $tabCondition = explode('.', $cond);
        
        if (!isset($tabCondition[1])) {
            $tabCondition[1] = $tabCondition[0];
            $tabCondition[0] = $this->getAdapter()->quoteTableAs($this->getTable()->info('name'));
        }
        
        $tabCondition = array_map('trim', $tabCondition);
        
        if (false === strpos($tabCondition[0], '`')) {
            $tabCondition[0] = $this->getAdapter()->quoteIdentifier($tabCondition[0]);
        }
        
        if (false === strpos($tabCondition[1], '`')) {
            $tabCondition[1] = $this->getAdapter()->quoteIdentifier($tabCondition[1]);
        }
        
        
        return implode('.', $tabCondition);
    }
    
    public function normalizeCondition($cond)
    {
        if (trim($cond) == '') {
            return $cond;
        }
        
        $tabCond = preg_split('` *((?:[<>]=?)|=) *`', $cond, 2, PREG_SPLIT_DELIM_CAPTURE);
        
        if (count($tabCond) != 3) {
            throw new Exception('Problem. Condition not supported');
        }
        
        $tabCond[0] = $this->forcePrefix(trim($tabCond[0]));
        $tabCond[2] = $this->forcePrefix($tabCond[2]);
        
        if (strcmp($tabCond[0], $tabCond[2]) > 0) {
            switch($tabCond[1]) {
                case '=':
                    break;
                case '<':
                    $tabCond[1] = '>';
                    break;
                case '<=':
                    $tabCond[1] = '>=';
                    break;
                case '>':
                    $tabCond[1] = '<';
                    break;
                case '>=':
                    $tabCond[1] = '<=';
                    break;
            }
            
            $temp = $tabCond[0];
            $tabCond[0] = $tabCond[2];
            $tabCond[2] = $temp;
        }
        
        return implode(' ', $tabCond);
    }

    /**
     * @param $colName
     * @return bool
     * @deprecated Us IsInQuery instead
     */
    public function hasColumn($colName)
    {
        return $this->isInQuery($colName);
    }

    /**
     * Generate a unique correlation name
     *
     * @param string|array $name A qualified identifier.
     * @return string A unique correlation name.
     */
    protected function _uniqueCorrelation($name)
    {
        if (is_array($name)) {
            $c = end($name);
        } else {
            // Extract just the last name of a qualified table name
            $dot = strrpos($name,'.');
            $c = ($dot === false) ? $name : substr($name, $dot+1);
        }
        for ($i = 2; array_key_exists($c, $this->_parts[self::FROM]); ++$i) {
            $c = $name . '_' . (string) $i;
        }

        return $c;
    }
    /**
     * Add join statement to query according to the given rule (the rule must be a many-to-many)
     *
     * @param string $rule the rule to apply
     * @param Centurion_Db_Table_Abstract [OPTIONAL] $localTable the model where to look for the rule if omit the table from $this is taken
     * @param boolean $full due to many-to-many relations this function may add two JOIN statement ($full = true), if false only one JOIN statement with the intersection table will be done
     * @param array $cols
     * @param string $joinType
     * @return string The qutoed table name
     */
    public function addManyToManyTable($rule, $localTable = null, $full = true,
        $cols = null, $joinType = self::JOIN_TYPE_INNER
    ) {
        if (null === $localTable)
            $localTable = $this->getTable();

        if (!($localTable instanceof Centurion_Db_Table_Abstract)) {
            if (null != $full && !is_bool($full)) {
                $cols = (array) $full;
            }
            $full = (bool) $localTable;
            $localTable = $this->getTable();
        }

        $manyRefMap = $localTable->info(Centurion_Db_Table_Abstract::MANY_DEPENDENT_TABLES);

        if (!isset($manyRefMap[$rule]) || !is_array($manyRefMap[$rule]))
            throw new Centurion_Db_Exception(sprintf('%s did not match any many-to-many rule from the model %s', $rule, get_class($localTable)));

        $ref = $manyRefMap[$rule];

        $localTableName = $localTable->info(Centurion_Db_Table_Abstract::NAME);
        $localPrimary = $localTable->info(Centurion_Db_Table_Abstract::PRIMARY);

        $interTable = Centurion_Db::getSingletonByClassName($ref['intersectionTable']);
        $interTableName = $interTable->info(Centurion_Db_Table_Abstract::NAME);

        $refTable = Centurion_Db::getSingletonByClassName($ref['refTableClass']);
        $refTableName = $refTable->info(Centurion_Db_Table_Abstract::NAME);
        $refPrimary = $refTable->info(Centurion_Db_Table_Abstract::PRIMARY);

        if (count($localPrimary) != 1) {
            throw new Centurion_Db_Exception('Can\'t add filter from many-to-many rule, local model %s has a composed primary key', get_class($localTable));
        }

        $localPrimary = array_shift($localPrimary);

        if (count($refPrimary) != 1) {
            throw new Centurion_Db_Exception('Can\'t add filter from many-to-many rule, foreign model %s has a composed primary key', $ref['refTableClass']);
        }

        $refPrimary = array_shift($refPrimary);

        if (!in_array($joinType, array_keys(self::$_joinMethod))) {
            throw new Centurion_Db_Exception(sprintf('Unknown join type : %s', $joinType));
        }

        $method = self::$_joinMethod[$joinType];

        $tableName = $this->_adapter->quoteIdentifier($interTableName);

        do {
            $joinCond = sprintf('%s.%s = %s.%s', $tableName,
                                                 $this->_adapter->quoteIdentifier($ref['columns']['local']),
                                                 $this->_adapter->quoteIdentifier($localTableName),
                                                 $this->_adapter->quoteIdentifier($localPrimary));

            $tableName = $this->_uniqueCorrelation($interTableName);
        } while ($this->_isAlreadyJoined($tableName, $joinCond));

        if (!$this->_isAlreadyJoined($interTableName, $joinCond)) {
            /**
             * We will join to the table. We check that Zend will not generate a autoatic alias because in this case
             * we must change the join condition
             */
            if ($interTableName !== $this->_uniqueCorrelation($interTableName)) {
                $quotedInterTableName = $this->_adapter->quoteIdentifier($this->_uniqueCorrelation($interTableName));
                $joinCond = sprintf('%s.%s = %s.%s', $quotedInterTableName,
                                                 $this->_adapter->quoteIdentifier($ref['columns']['local']),
                                                 $this->_adapter->quoteIdentifier($localTableName),
                                                 $this->_adapter->quoteIdentifier($localPrimary));
            } else {
                $quotedInterTableName = $this->_adapter->quoteIdentifier($interTableName);
            }

            $this->$method($interTableName, $joinCond, array());
        }

        $joinCond = sprintf('%s.%s = %s.%s', $interTableName,
                                             $this->_adapter->quoteIdentifier($ref['columns']['foreign']),
                                             $this->_adapter->quoteIdentifier($refTableName),
                                             $this->_adapter->quoteIdentifier($refPrimary));

        $quotedRefTableName = $this->_adapter->quoteIdentifier($refTableName);

        if ($full && !$this->_isAlreadyJoined($refTableName, $joinCond)) {
            /**
             * We will join to the table. We check that Zend will not generate a autoatic alias because in this case
             * we must change the join condition
             */
            if ($refTableName !== $this->_uniqueCorrelation($refTableName)) {
                $quotedRefTableName = $this->_adapter->quoteIdentifier($this->_uniqueCorrelation($refTableName));
                $joinCond = sprintf('%s.%s = %s.%s', $quotedInterTableName,
                                             $this->_adapter->quoteIdentifier($ref['columns']['foreign']),
                                             $quotedRefTableName,
                                             $this->_adapter->quoteIdentifier($refPrimary));
            }
            $this->$method($refTableName, $joinCond, array());
        }

        if ($cols) {
            $this->_tableCols($localTableName, $cols, true);
        }

        return $quotedRefTableName;
    }

    /**
     * Add join statement to query according to the given rule (the rule must be a referenceMap)
     *
     * @param string $rule
     * @param Centurion_Db_Table_Abstract [OPTIONAL] $localTable
     * @param array $cols
     * @param string $joinType
     * @return Centurion_Db_Table_Select
     */
    public function addDependentTable($rule, $localTable = null, $cols = null, $joinType = self::JOIN_TYPE_INNER)
    {
        if (null == $localTable) {
            $localTable = $this->getTable();
        }
        
        if (!($localTable instanceof Centurion_Db_Table_Abstract)) {
            $cols = (array) $localTable;
            $localTable = $this->getTable();
        }

        $localTableName = $localTable->info(Centurion_Db_Table_Abstract::NAME);
        $refMap = $localTable->getReferenceMap($rule);

        $refTable = Centurion_Db::getSingletonByClassName($refMap['refTableClass']);
        $refTableName = $refTable->info(Zend_Db_Table::NAME);

        if (!in_array($joinType, array_keys(self::$_joinMethod))) {
            throw new Centurion_Db_Exception(sprintf("unknown join type : %s", $joinType));
        }

        $tableName = $this->_adapter->quoteIdentifier($refTableName);

        // Caster les variables en tableau, utile quand une seule colonne est précisé dans la referenceMap
        $refMap['refColumns'] = (array) $refMap['refColumns'];
        $refMap['columns'] = (array) $refMap['columns'];

        // Création de la condition de jointure pour la referenceMap
        $joinCond = array();
        foreach ($refMap['refColumns'] as $key => $refColumn) {
            $joinCond[] = sprintf('%s.%s = %s.%s', $tableName,
                $this->_adapter->quoteIdentifier($refColumn),
                $this->_adapter->quoteIdentifier($localTableName),
                $this->_adapter->quoteIdentifier($refMap['columns'][$key]));
        }
        /* Dans le cas où il y a plusieurs colonnes dans la referenceMap, créer une string avec toutes les conditions
         * du tableau $joinCond
         */
        $joinCond = implode(' AND ', $joinCond);

        if (!$this->_isAlreadyJoined($refTableName, $joinCond)) {
            $method = self::$_joinMethod[$joinType];
            /**
             * We will join to the table. We check that Zend will not generate a autoatic alias because in this case
             * we must change the join condition
             */ 
            if ($refTableName !== $this->_uniqueCorrelation($refTableName)) {
                $tableName = $this->_adapter->quoteIdentifier($this->_uniqueCorrelation($refTableName));
                $joinCond = sprintf('%s.%s = %s.%s', $tableName,
                        $this->_adapter->quoteIdentifier($refMap['refColumns']),
                        $this->_adapter->quoteIdentifier($localTableName),
                        $this->_adapter->quoteIdentifier($refMap['columns']));
            }
            
            $this->$method($refTableName, $joinCond, array());
        }

        if ($cols) {
            $this->_tableCols($localTableName, $cols, true);
        }

        return $tableName;
    }

    /**
     * Add join statement to query according to the given rule
     * the rule must be in the dependentTables array, and the joined table must have a reference map to firsts
     * and the columns from the dependant table should have an unique constraint
     *
     * @param string $rule
     * @param Centurion_Db_Table_Abstract [OPTIONAL] $localTable
     * @param array $cols
     * @param string $joinType
     * @param string $foreignRule
     */
    public function addOneToOneTable($rule, $localTable = null, $cols = null, $joinType = self::JOIN_TYPE_INNER, $foreignRule = null)
    {
        if (null == $localTable)
            $localTable = $this->getTable();

        if (!($localTable instanceof Centurion_Db_Table_Abstract)) {
            $cols = (array) $localTable;
            $localTable = $this->getTable();
        }

        $localTableClass = get_class($localTable);
        $localTableName = $localTable->info(Centurion_Db_Table_Abstract::NAME);
        $dependentTables = $localTable->getDependentTables();
        $depTableClassName = $dependentTables[$rule];
        $depTable = Centurion_Db::getSingletonByClassName($depTableClassName);
        $depTableName = $depTable->info(Centurion_Db_Table_Abstract::NAME);

        $tableName = $depTableName;
        
        $foreignRefMap = $depTable->getReferenceMap();
        if (null == $foreignRule) {
            foreach ($foreignRefMap as $refRule) {
                if ($refRule['refTableClass'] == $localTableClass) {
                    break;
                }
            }

            if ($refRule['refTableClass'] != $localTableClass) {
                throw new Centurion_Db_Exception(sprintf('The foreign reference rules couldn\'t be found in %s', $depTableClassName));
            }

        } else {
            if (!in_array($foreignRule, array_keys($foreignRefMap))) {
                throw new Centurion_Db_Exception('no such rule (%s) for model %s', $foreignRule, $depTableClassName);
            }

            $refRule = $foreignRefMap[$foreignRule];
        }

        if (!in_array($joinType, array_keys(self::$_joinMethod))) {
            throw new Centurion_Db_Exception(sprintf('unknown join type : %s', $joinType));
        }

        $joinCond = sprintf('%s.%s = %s.%s', $this->_adapter->quoteIdentifier($tableName),
                                             $this->_adapter->quoteIdentifier($refRule['columns']),
                                             $this->_adapter->quoteIdentifier($localTableName),
                                             $this->_adapter->quoteIdentifier($refRule['refColumns']));
        
        if (!$this->_isAlreadyJoined($depTableName, $joinCond)) {
            $method = self::$_joinMethod[$joinType];
            /**
             * We will join to the table. We check that Zend will not generate a autoatic alias because in this case
             * we must change the join condition
             */
            if ($tableName !== $this->_uniqueCorrelation($tableName)) {
                $tableName = $this->_adapter->quoteIdentifier($this->_uniqueCorrelation($tableName));
                $joinCond = sprintf('%s.%s = %s.%s', $tableName,
                        $this->_adapter->quoteIdentifier($refRule['columns']),
                        $this->_adapter->quoteIdentifier($localTableName),
                        $this->_adapter->quoteIdentifier($refRule['refColumns']));
            }
            $this->$method($depTableName, $joinCond, array());
        }

        if ($cols) {
            $this->_tableCols($localTableName, $cols, true);
        }

        return $tableName;
    }

    /**
     * Add filters for external fields
     *
     * columnsString should be formated like this (with the default separator) : rule1[__ruleN[__field]]
     * ex : - formations__category__name
     *      - formations__title
     *      - formations
     *
     * @param string $columnString rules which permit to determine tables
     * @param string [OPTIONAL] $separator rules are separated by default by "__"
     */
    public function addRelated($columnString, $separator = self::RULES_SEPARATOR)
    {
        $arrayCol = explode($separator, $columnString);
        $sqlField = null;

        $localTable = $this->getTable();
        $foreignTable = null;
        $nextRule = null;
        $uniqName = null;
        
        do {
            $rule = array_shift($arrayCol);
            $rule = explode(self::JOIN_TYPE_SEPARATOR, $rule);

            if (!isset($rule[1]) || !$rule[0]) {
                $rule[1] = $rule[0];
                $rule[0] = self::JOIN_TYPE_INNER;
            }

            if (!in_array($rule[0], array_keys(self::$_joinMethod)))
                throw new Centurion_Db_Table_Exception(sprintf('%s is not a valid join type', $rule[0]));

            $manyToManyMap = $localTable->info(Centurion_Db_Table_Abstract::MANY_DEPENDENT_TABLES);
            $dependentRefMap = $localTable->info(Centurion_Db_Table_Abstract::REFERENCE_MAP);
            $dependentTables = $localTable->info(Centurion_Db_Table_Abstract::DEPENDENT_TABLES);

            $remainingRules = count($arrayCol);
            $nextRule = ($remainingRules ? $arrayCol[0] : null);

            if (in_array($rule[1], array_keys($manyToManyMap))) {
                $full = true;
                $foreignTable = Centurion_Db::getSingletonByClassName($manyToManyMap[$rule[1]]['refTableClass']);
                if (null === $nextRule) {
                    $full = false;
                    $interTable = Centurion_Db::getSingletonByClassName($manyToManyMap[$rule[1]]['intersectionTable']);
                    $sqlField = sprintf('%s.%s', $this->_adapter->quoteIdentifier($interTable->info(Centurion_Db_Table_Abstract::NAME)),
                                                 $this->_adapter->quoteIdentifier($manyToManyMap[$rule[1]]['columns']['foreign']));
                }

                $uniqName = $this->addManyToManyTable($rule[1], $localTable, $full, array(), $rule[0]);
            } elseif (in_array($rule[1], array_keys($dependentRefMap))) {
                if (null !== $nextRule) {
                    $foreignTable = Centurion_Db::getSingletonByClassName($dependentRefMap[$rule[1]]['refTableClass']);
                    $uniqName = $this->addDependentTable($rule[1], $localTable, array(), $rule[0]);
                } else {
                    $sqlField = sprintf('%s.%s', $uniqName,
                                                 $this->_adapter->quoteIdentifier($dependentRefMap[$rule[1]]['columns']));
                }
            } elseif (in_array($rule[1], array_keys($dependentTables))) {
                $foreignTable = Centurion_Db::getSingletonByClassName($dependentTables[$rule[1]]);
                $uniqName = $this->addOneToOneTable($rule[1], $localTable, array(), $rule[0]);
            } else {
                throw new Centurion_Db_Exception(sprintf('%s did not match any external rule', $rule[1]));
            }

            if (null === $sqlField && in_array($nextRule, $foreignTable->info(Centurion_Db_Table_Abstract::COLS))) {
                $sqlField = sprintf('%s.%s', $uniqName, $this->_adapter->quoteIdentifier($nextRule));
            }

            $localTable = $foreignTable;
        } while ($remainingRules && null === $sqlField);

        if (null === $sqlField) {
            $sqlField = sprintf('%s.%s', $uniqName,
                                         $this->_adapter->quoteIdentifier($foreignTable->info(Centurion_Db_Table_Abstract::PRIMARY)));
        }

        return $sqlField;
    }

    /**
     * build a select that filters values according to arguments
     *
     * @param array $kwargs an array of filters clauses
     * @todo allow pass juste string
     * @todo phpdoc to explain who to use it
     * @return Centurion_Db_Table_Select
     */
    public function filter(array $kwargs)
    {
        foreach ($kwargs as $key => $value) {
            if (is_int($key) && is_array($value)) {
                $key = $value[0];
                $value = $value[1];
            }
            
            if (is_numeric($key)) {
                $this->where($value);
                continue;
            }

            if ($key instanceof Zend_Db_Expr) {
                $this->where($key, $value);
                continue;
            }

            $sqlValue = '';
            $sqlAssert = '';
            $suffix = '';
            $method = 'where';
            $altSuffix = '';
            $sqlColumnPattern = '';


            if (!strncmp($key, self::OPERATOR_OR, strlen(self::OPERATOR_OR))) {
                $key = substr($key, strlen(self::OPERATOR_OR));
                $method = 'orWhere';
            }

            $tmpSqlColumn = $key;
            //If it's a negative condition
            if (!strncmp($key, self::OPERATOR_NEGATION, strlen(self::OPERATOR_NEGATION))) {
                //We remove the prefix from the
                $tmpSqlColumn = substr($tmpSqlColumn, strlen(self::OPERATOR_NEGATION));
                $sqlNegativeAssert = true;
            } else {
                $sqlNegativeAssert = false;
            }

            $separatorPos = strlen($tmpSqlColumn);
            if (false !== strpos($tmpSqlColumn, self::RULES_SEPARATOR)) {
                $separatorPos = strrpos($tmpSqlColumn, self::RULES_SEPARATOR);
                $suffix = substr($tmpSqlColumn, ($separatorPos + strlen(self::RULES_SEPARATOR)));
            }

             while (in_array($suffix, self::$_suffixes)) {
                 list($negative, $positive, $pattern) = self::$_operators[$suffix];

                 $sqlAssert = $sqlNegativeAssert ? $negative : $positive;

                 if ($suffix === self::OPERATOR_IN) {
                     $sqlValue = sprintf($pattern, $this->_adapter->quote($value));
                 } else if ($suffix === self::OPERATOR_ISNULL) {
                     $sqlValue = 'NULL';
                 } else if ($suffix === self::OPERATOR_RANGE) {
                     if (!is_array($value) || count($value) != 2) {
                         throw new Centurion_Db_Table_Exception('Value must be an array that contains 2 elements with range operator');
                     }
                     if (!$value[0]) {
                         $value[0] = $value[1];
                         $altSuffix = self::OPERATOR_LESS_EQUAL;
                         $sqlNegativeAssert = false;
                     } elseif (!$value[1]) {
                         $altSuffix = self::OPERATOR_GREATER_EQUAL;
                         $sqlNegativeAssert = false;
                     } else {
                         $sqlValue = sprintf($pattern, $this->_adapter->quote($value[0]), $this->_adapter->quote($value[1]));
                     }
                 } else if (null !== $pattern) {
                     $sqlColumnPattern = $pattern;
                 }

                if (!$altSuffix) {
                    $tmpSqlColumn = substr($tmpSqlColumn, 0, $separatorPos);

                    if (false !== strpos($tmpSqlColumn, self::RULES_SEPARATOR)) {
                        $separatorPos = strrpos($tmpSqlColumn, self::RULES_SEPARATOR);
                        $suffix = substr($tmpSqlColumn, ($separatorPos + strlen(self::RULES_SEPARATOR)));
                    } else {
                        $suffix = '';
                    }
                } else {
                    $suffix = $altSuffix;
                    $altSuffix = '';
                }

            }

            if (!$sqlValue) {
                if ($value instanceof Zend_Db_Expr) {
                    $sqlValue = $value;
                } else {
                    $value = (array) $value;
                    $value = reset($value);
                    $sqlValue = $this->_adapter->quote($value);
                }
            }

            if (!$sqlAssert) {
                list($negative, $positive, ) = self::$_operators[self::DEFAULT_OPERATOR];
                $sqlAssert = $sqlNegativeAssert ? $negative : $positive;
            }

            // check if field is natural or external
            $natural = $this->getTable()->info(Centurion_Db_Table_Abstract::COLS);
            if (in_array($tmpSqlColumn, $natural)) {
                $name = $this->_table->info(Centurion_Db_Table_Abstract::NAME);

                //We look for any alias for this table
                foreach ($this->_parts[self::FROM] as $key => $from) {
                    if ($from['tableName'] == $name && $from['joinType'] == 'inner join') {
                        $name = $key;
                        break;
                    }
                }
                $sqlColumn = sprintf('%s.%s',
                                     $this->_adapter->quoteIdentifier($name),
                                     $this->_adapter->quoteIdentifier($tmpSqlColumn));
            } else {
                $sqlColumn = $this->addRelated($tmpSqlColumn);
            }

            if (!empty($sqlColumnPattern)) {
                $sqlColumn = sprintf($sqlColumnPattern, $sqlColumn);
            }

            //We make the join with calculated condition
            call_user_func_array(array($this, $method), array(sprintf('%s %s %s', $sqlColumn, $sqlAssert, $sqlValue)));
        }

        return $this;
    }

    /**
     * @param string $cols Columns to find
     * @param string $tableName table name of column
     */
    public function isInQuery($colname, $tableName = null)
    {
        $internCols = $this->getPart(self::COLUMNS);

        foreach($internCols as $col) {
            if ($tableName === null || $tableName === $col[0]) {
                if ($col[1] === '*' || $col[2] === $colname
                    || ($col[2] === null && is_string($col[1]) && $col[1] === $colname)
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getColumnsName()
    {
        $cols = array();
        $internCols = $this->getPart(self::COLUMNS);

        foreach ($internCols as $col) {
            $cols[] = (isset($col[3])) ? $col : (string) $col[2];
        }

        return $cols;
    }

    /**
     * @see Zend_Db_Table_Abstract::fetchAll();
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    public function fetchAll($order = null, $count = null, $offset = null)
    {
        return $this->getTable()->fetchAll($this, $order, $count, $offset);
    }

    /**
     * @see Zend_Db_Table_Abstract::fetchAll();
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    public function all($order = null, $count = null, $offset = null)
    {
        return $this->fetchAll($order, $count, $offset);
    }

    public function joinInner($name, $cond, $cols = self::SQL_WILDCARD, $schema = null)
    {
        parent::joinInner($name, $cond, $cols, $schema);
        Centurion_Signal::factory('on_select_joinInner')->send($this, array($this, $name));
        return $this;
    }

    /**
     * @see Zend_Db_Table_Abstract::fetchRow();
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @return Zend_Db_Table_Row_Abstract|null The row results per the
     *     Zend_Db_Adapter fetch mode, or null if no row found.
     */
    public function fetchRow($order = null)
    {
        return $this->getTable()->fetchRow($this, $order);
    }

    /**
     *
     * Exclude a Row or a RowSet from the current select.
     *
     * @param Zend_Db_Table_Row_Abstract|Zend_Db_Table_RowSet_Abstract $rowSet
     */
    public function not($rowSet)
    {
        if ($rowSet instanceof Zend_Db_Table_Row_Abstract) {
            $rowSet = array($rowSet);
        }

        $primaries = $this->_table->info(Centurion_Db_Table_Abstract::PRIMARY);

        foreach ($rowSet as $row) {
            $conditions = array();
            foreach ($primaries as $primary) {
                $conditions[] = $this->getAdapter()->quoteIdentifier($primary) . ' = ' . $this->getAdapter()->quote($row->{$primary});
            }
            $this->where('!(' . implode(' and ', $conditions). ')');
        }

        return $this;
    }

    /**
     *
     * @param $dependences
     * @todo accepter une reférence "lointaine" (ex user__profile__avatar)
     * @return Centurion_Db_Table_Select
     */
    public function hydrate($dependences, $joinType = self::JOIN_TYPE_LEFT)
    {
        $this->setIntegrityCheck(false);

        $dependences = (array) $dependences;

        foreach ($dependences as $key => $dependence) {
            $refColsList = null;
            if (!is_int($key)) {
                $refColsList = $dependence;
                $dependence = $key;
            }
            
            $this->_hydratedDependences[] = $dependence;
            
            $refTable = $this->getTable();
            
            //TODO: here insert a do while for reference like user__profile__avatar
            $refMap = $refTable->getReferenceMap($dependence);

            $refTable = Centurion_Db::getSingletonByClassName($refMap['refTableClass']);
            $refTableName = $refTable->info(Zend_Db_Table::NAME);

            if ($refColsList == null) {
                $refColsList = Centurion_Db::getSingletonByClassName($refMap['refTableClass'])->info(Zend_Db_Table::COLS);
            }
                
            $cols = array();
            
            foreach ($refColsList as $col) {
                $cols[] = $refTableName . '.' . $col . ' ' . self::SQL_AS . ' ' . $dependence . self::HYDRATE_SEPARATOR . $col;
            }

            $this->addDependentTable($dependence, null, $cols, self::JOIN_TYPE_LEFT);
        }

        return $this;
    }

    public function getHydratedDependences()
    {
        return $this->_hydratedDependences;
    }

    /**
     * @see Zend_Db_Table_Abstract::fetchRow();
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @return Zend_Db_Table_Row_Abstract|null The row results per the
     *     Zend_Db_Adapter fetch mode, or null if no row found.
     */
    public function get($order = null)
    {
        return $this->fetchRow($order);
    }

    /**
     * Adds a row order to the query.
     *
     * @param mixed $spec The column(s) and direction to order by.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function order($spec)
    {
        if (!is_array($spec)) {
            $spec = array($spec);
        }

        // force 'ASC' or 'DESC' on each order spec, default is ASC.
        foreach ($spec as $val) {
            if ($val instanceof Zend_Db_Expr) {
                $expr = $val->__toString();
                if (empty($expr)) {
                    continue;
                }
                $this->_parts[self::ORDER][] = $val;
            } else {
                if (empty($val)) {
                    continue;
                }
                $direction = self::SQL_ASC;
                if (preg_match('/(.*\W)(' . self::SQL_ASC . '|' . self::SQL_DESC . ')\b/si', $val, $matches)) {
                    $val = trim($matches[1]);
                    $direction = $matches[2];
                }

                if (preg_match('/\(.*\)/', $val)) {
                    $val = new Zend_Db_Expr($val);
                } else {
                    if (strpos($val, self::RULES_SEPARATOR) !== false) {
                        $val = new Zend_Db_Expr($this->addRelated($val));
                    }
                }
                $this->_parts[self::ORDER][] = array($val, $direction);
            }
        }

        return $this;
    }


    /**
     * Turn magic function calls into non-magic function calls
     * for joinUsing syntax
     *
     * @param string $method
     * @param array $args OPTIONAL Zend_Db_Table_Select query modifier
     * @return Zend_Db_Select
     * @throws Zend_Db_Select_Exception If an invalid method is called.
     */
    public function __call($method, array $args)
    {
        $matches = array();

        /**
         * Recognize methods for Has-Many cases:
         * findParent<Class>()
         * findParent<Class>By<Rule>()
         * Use the non-greedy pattern repeat modifier e.g. \w+?
         */
        if (preg_match('/^join([a-zA-Z]*?)Using([a-zA-Z]*?)$/', $method, $matches)) {
            $type = strtolower($matches[1]);
            if ($type) {
                $type .= ' join';
                if (!in_array($type, self::$_joinTypes)) {
                    require_once 'Zend/Db/Select/Exception.php';
                    throw new Zend_Db_Select_Exception("Unrecognized method '$method()'");
                }
                if (in_array($type, array(self::CROSS_JOIN, self::NATURAL_JOIN))) {
                    require_once 'Zend/Db/Select/Exception.php';
                    throw new Zend_Db_Select_Exception("Cannot perform a joinUsing with method '$method()'");
                }
            } else {
                $type = self::INNER_JOIN;
            }
            array_unshift($args, $type);

            if (!isset($matches[2])) {
                return call_user_func_array(array($this, '_joinUsing'), $args);
            }

            array_unshift($args, $matches[2]);

            return call_user_func_array(array($this, '_joinUsingVia'), $args);
        }

        require_once 'Zend/Db/Select/Exception.php';
        throw new Zend_Db_Select_Exception("Unrecognized method '$method()'");
    }

     /**
      * Adds a JOIN table and columns to the query with the referenceMap.
      *
      * @param  string $key                 The referenceMap key
      * @param  array|string $cols          The columns to select from the joined table.
      * @param  string $schema              The database name to specify, if any.
      * @return Centurion_Db_Table_Select   This Centurion_Db_Table_Select object.
      */
    protected function _joinUsingVia($key, $type, $cols = '*', $schema = null)
    {
        if (empty($this->_parts[self::FROM])) {
            require_once 'Zend/Db/Select/Exception.php';
            throw new Zend_Db_Select_Exception("You can only perform a joinUsing after specifying a FROM table");
        }

        $referenceMap = $this->getTable()->getReferenceMap(Centurion_Inflector::tableize($key));
        $refTable = Centurion_Db::getSingletonByClassName($referenceMap['refTableClass']);

        $name = $refTable->info('name');

        $join = $this->_adapter->quoteIdentifier(key($this->_parts[self::FROM]), true);
        $from = $this->_adapter->quoteIdentifier($name);

        $primary = $refTable->info('primary');

        $cond1 = $join . '.' . $referenceMap['columns'];
        $cond2 = $from . '.' . $primary[1];
        $cond  = $cond1 . ' = ' . $cond2;

        return $this->_join($type, $name, $cond, $cols, $schema);
    }
}
