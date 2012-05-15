<?php
class Core_Traits_Mptt_Model_DbTable extends Centurion_Traits_Model_DbTable_Abstract
{
    const POSITION_LAST_CHILD = 'last-child';
    const POSITION_FIRST_CHILD = 'first-child';
    const POSITION_LEFT = 'left';
    const POSITION_RIGHT = 'right';

    const MPTT_LEFT = 'mptt_lft';
    const MPTT_RIGHT = 'mptt_rgt';
    //const MPTT_PARENT_REFERENCE = 'parentReference'; // ref rule
    const MPTT_PARENT = 'mptt_parent_id';
    const MPTT_LEVEL = 'mptt_level';
    const MPTT_TREE = 'mptt_tree_id';

    protected $_requiredColumns = array(self::MPTT_LEFT, self::MPTT_LEVEL, self::MPTT_PARENT, self::MPTT_RIGHT, self::MPTT_TREE);

    protected $_parentRefRule = 'mptt_parent';

    /**
     * Variables.
     *
     * @var array
     */
    protected $_variables;

    protected $_recursiveDelete = true;

    public function query($sql)
    {
        $return = $this->getAdapter()->query($sql);

        Centurion_Signal::factory('clean_cache')->send($this, array(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('__' . $this->info(Centurion_Db_Table_Abstract::NAME))));

        return $return;
    }

    public function init()
    {
        if (!class_implements($this->_rowClass, 'Core_Traits_Mptt_Model_DbTable_Row_Interface'))
            throw new Centurion_Traits_Exception(sprintf('Class %s defined as row class for model %s must implement Core_Traits_Mptt_Model_DbTable_Row_Interface',
                                                          $this->_rowClass, get_class($this->_model)));

        $pk = $this->_modelInfo[Centurion_Db_Table_Abstract::PRIMARY];
        if (is_array($pk) && 1 != count($pk))
            throw new Centurion_Traits_Exception('Trait MPTT doesn\'t support compound primary key');

        $pk = (array) $pk;

        $primaryKeyCol = array_shift($pk);

        $this->_parentRefRule = $this->_addReferenceMapRule('parent', self::MPTT_PARENT, get_class($this->_model), 'id',  array('onDelete' => Centurion_Db_Table_Abstract::SET_NULL, 'onUpdate' => Centurion_Db_Table_Abstract::CASCADE));

        $this->_childrenRefRule = $this->_addDependentTables('children', get_class($this->_model));

        $this->_variables = array('%left%'          => self::MPTT_LEFT,
                                  '%right%'         => self::MPTT_RIGHT,
                                  '%parentColumn%'  => self::MPTT_PARENT,
                                  '%level%'         => self::MPTT_LEVEL,
                                  '%tree%'          => self::MPTT_TREE,
                                  '%pk%'            => $primaryKeyCol);

        Centurion_Signal::factory('on_dbTable_select')->connect(array($this, 'onSelect'), $this->_model);
    }

    public function isRecursiveDelete()
    {
        return $this->_recursiveDelete;
    }

    public function getParentRefRule()
    {
        return $this->_parentRefRule;
    }

    public function onSelect($signal, $sender, $select, $applyDefaultFilters)
    {
        $name = $this->info('name');

        $select->order($name . '.' . self::MPTT_TREE)
               ->order($name . '.' . self::MPTT_LEFT);
    }

    /**
     * Sets up the tree state for node (which has not yet been inserted into in the database) so it will be positioned relative
     * to a given target node as specified by position (when appropriate) it is inserted, with any necessary space already
     * having been made for it.
     *
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $node The node object
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $target A null target indicates that node should be the last root node
     * @param string $position Valid values are first-child, last-child, left or right
     * @param bool $commit If commit is true, node's save() method will be called before it is returned
     * @return Core_Traits_Mptt_Model_DbTable_Row_Interface
     */
    public function insertNode($node, $target = null, $position = self::POSITION_LAST_CHILD, $commit = false)
    {
        if ($node->pk) {
            throw new Centurion_Db_Exception('Cannot insert a node which has already been saved.');
        }

        if (null === $target) {
            $node->setLeft(1);
            $node->setRight(2);
            $node->setLevel(0);
            $node->setTreeId($this->_getNextTreeId());

            $node->setParentId(null);
        } else {
            if ($target->isRootNode() && in_array($position, array(self::POSITION_LEFT, self::POSITION_RIGHT))) {
                $targetTreeId = $target->getTreeId();

                if ($position == self::POSITION_LEFT) {
                    $treeId = $targetTreeId;
                    $spaceTarget = $targetTreeId - 1;
                } else {
                    $treeId = $targetTreeId + 1;
                    $spaceTarget = $targetTreeId;
                }

                $this->_createTreeSpace($spaceTarget);

                $node->setLeft(1);
                $node->setRight(2);
                $node->setLevel(0);
                $node->setTreeId($treeId);
                $node->setParentId(null);
            } else {
                $node->setLeft(0);
                $node->setLevel(0);

                list($spaceTarget, $level, $left, $parent) = $this->_calculateInterTreeMoveValues($node, $target, $position);

                $treeId = $parent->getTreeId();

                $this->_createSpace(2, $spaceTarget, $treeId);

                $node->setLeft(-$left);
                $node->setRight(-$left + 1);
                $node->setLevel(-$level);
                $node->setTreeId($treeId);
                $node->setParentId($parent->pk);
            }
        }

        if ($commit) {
            $node->save();
        }

        return $node;
    }

    /**
     * Moves node relative to a given target node as specified by position (when appropriate),
     * by examining both nodes and calling the appropriate method to perform the move.
     *
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $node The node object who will be modified to reflect its new tree state in the database
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $target A null target indicates that node should be the last root node
     * @param string $position Valid values are first-child, last-child, left or right
     * @return void
     */
    public function moveNode($node, $target = null, $position = self::POSITION_LAST_CHILD)
    {
        if (null === $target) {
            if ($node->isChildNode()) {
                return $this->_makeChildRootNode($node);
            }
        } elseif ($target->isRootNode() && in_array($position, array(self::POSITION_LEFT, self::POSITION_RIGHT))) {
            return $this->_makeSiblingOfRootNode($node, $target, $position);
        } else {
            if ($node->isRootNode()) {
                return $this->_moveRootNode($node, $target, $position);
            } else {
                return $this->_moveChildNode($node, $target, $position);
            }
        }
    }

    /**
     * Returns the root node of the tree with the given id.
     *
     * @param int $treeId Specified treeId
     * @return Core_Traits_Mptt_Model_DbTable_Row_Interface
     */
    public function getRootNode($treeId)
    {
        return $this->get(array(self::MPTT_TREE => $treeId,
                                sprintf("%s", self::MPTT_LEFT) => 1));
    }

    /**
     * Creates a Centurion_Db_Table_Select containing root nodes.
     *
     * @return Centurion_Db_Table_Select
     * @deprecated Because it's name is not conventional. @see Core_Traits_Mptt_Model_DbTable::getRootNodesSelect()
     */
    public function getRootNodes()
    {
        return $this->getRootNodesSelect();
    }

    /**
     * Creates a Centurion_Db_Table_Select containing root nodes.
     *
     * @return Centurion_Db_Table_Select
     */
    public function getRootNodesSelect()
    {
        Centurion_Db_Table_Abstract::setFiltersStatus(true);
        $select = $this->select(true, true)->filter(array(sprintf("%s", self::MPTT_LEFT) => 1));
        Centurion_Db_Table_Abstract::restoreFiltersStatus();
        return $select;
    }

    /**
     * Calculates values required when moving node relative to target as specified by position.
     *
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $node The node object which will be moved
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $target The target object
     * @param string $position Valid values are first-child, last-child, left or right
     * @return array
     */
    protected function _calculateInterTreeMoveValues($node, $target, $position)
    {
        $left = $node->getLeft();
        $level = $node->getLevel();
        $targetLeft = $target->getLeft();
        $targetRight = $target->getRight();
        $targetLevel = $target->getLevel();

        if (in_array($position, array(self::POSITION_LAST_CHILD, self::POSITION_FIRST_CHILD))) {
            if ($position === self::POSITION_LAST_CHILD) {
                $spaceTarget = $targetRight - 1;
            } else {
                $spaceTarget = $targetLeft;
            }

            $levelChange = $level - $targetLevel - 1;
            $parent = $target;
        } elseif (in_array($position, array(self::POSITION_LEFT, self::POSITION_RIGHT))) {
            if ($position === self::POSITION_LEFT) {
                $spaceTarget = $targetLeft - 1;
            } else {
                $spaceTarget = $targetRight;
            }

            $levelChange = $level - $targetLevel;
            $parent = $target->getParent();
        } else {
            throw new Centurion_Db_Exception(sprintf('Invalid position was given %s.', $position));
        }

        $leftRightChange = $left - $spaceTarget - 1;

        return array($spaceTarget, $levelChange, $leftRightChange, $parent);
    }

    /**
     * Closes a gap of a certain size after the given target point in the tree identified by treeId.
     *
     * @param int $size Size for closing the gap
     * @param int $target The target point
     * @param int $treeId The given target in the tree
     * @return Core_Traits_Mptt_Model_DbTable
     */
    protected function _closeGap($size, $target, $treeId)
    {
        return $this->_manageSpace(-$size, $target, $treeId);
    }

    /**
     * Creates a space of a certain size after the given target point in the tree identified by treeId.
     *
     * @param int $size Size for creating the gap
     * @param int $target The target point
     * @param int $treeId The given target in the tree
     * @return Core_Traits_Mptt_Model_DbTable
     */
    protected function _createSpace($size, $target, $treeId)
    {
        return $this->_manageSpace($size, $target, $treeId);
    }

    /**
     * Creates space for a new tree by incrementing all tree ids greater than targetTreeId.
     *
     * @param string $targetTreeId
     * @return $this
     */
    protected function _createTreeSpace($targetTreeId)
    {

            $createSpaceQuery = <<<EOF
UPDATE {$this->_name}
SET %tree% = %tree% + 1
WHERE %tree% > %s
EOF;
        $createSpaceQuery = sprintf($this->_attachVariables($createSpaceQuery),
                       $targetTreeId);
        $this->query($createSpaceQuery);
        return $this;
    }

    /**
     * Determines the next largest unused tree id for the tree managed by this manager.
     *
     * @param string $condition Condition added to the select
     * @return int
     */
    protected function _getNextTreeId($condition = null)
    {
        $name = $this->info('name');
        $select = $this->select()
                       ->from($this->_modelInfo[Centurion_Db_Table_Abstract::NAME], sprintf('MAX(%s.%s)', $name, self::MPTT_TREE));

        if (null !== $condition)
            $select->where($condition);
        $select->group(new Zend_Db_Expr('\'1\''));
        $result = $this->getAdapter()->fetchOne($select);

        return null !== $result ? $result + 1 : 1;
    }

    /**
     * Removes node from its current tree, with the given set of changes being applied to node and its descendants, closing
     * the gap left by moving node as it does so.
     * If parentPk is null, this indicates that node is being moved to a brand new tree as its root node, and will thus
     * have its parent field set to null. Otherwise, node will have parentPk set for its parent field.
     *
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $node
     * @param int $levelChange
     * @param int $leftRightChange
     * @param int $newTreeId
     * @param int $parentPk
     * @return $this
     */
    protected function _interTreeMoveAndCloseGap($node, $levelChange, $leftRightChange, $newTreeId, $parentPk = null)
    {
        $left = $node->getLeft();
        $right = $node->getRight();
        $gapSize = $right - $left + 1;
        $gapTargetLeft = $left - 1;

        $this->query($this->_getInterTreeMoveQuery($left, $right, $levelChange, $newTreeId,
                                                                 $leftRightChange, $gapTargetLeft, $gapSize, $node, $parentPk));

        return $this;
    }

    /**
     * Removes node from its tree, making it the root node of a new tree.
     *
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $node The node object
     * @param int $newTreeId If not specified, a new tree id will be generated
     * @return $this
     */
    protected function _makeChildRootNode($node, $newTreeId = null)
    {
        $left = $node->getLeft();
        $right = $node->getRight();
        $level = $node->getLevel();
        $treeId = $node->getTreeId();

        if (null === $newTreeId) {
            $newTreeId = $this->_getNextTreeId();
        }

        $leftRightChange = $left - 1;

        $this->_interTreeMoveAndCloseGap($node, $level, $leftRightChange, $newTreeId);

        $node->setLeft($left - $leftRightChange);
        $node->setRight($right - $leftRightChange);
        $node->setLevel(0);
        $node->setTreeId($newTreeId);
        $node->setParentId(null);

        return $this;
    }

    /**
     * Moves node, making it a sibling of the given target root node as specified by position.
     *
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $node The node object
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $target The target object
     * @param string $position Valid values are first-child, last-child, left or right
     * @return $this
     */
    protected function _makeSiblingOfRootNode($node, $target, $position)
    {
        if ($node->pk === $target->pk) {
            throw new Centurion_Db_Exception('A node may not be made a sibling of itself.');
        }

        $treeId = $node->getTreeId();
        $targetTreeId = $target->getTreeId();

        if ($node->isChildNode()) {
            if ($position === self::POSITION_LEFT) {
                $spaceTarget = $targetTreeId - 1;
                $newTreeId = $targetTreeId;
            } elseif ($position === self::POSITION_RIGHT) {
                $spaceTarget = $targetTreeId;
                $newTreeId = $targetTreeId + 1;
            } else {
                throw new Centurion_Db_Exception(sprintf('Invalid position was given: %s.', $position));
            }

            $this->_createTreeSpace($spaceTarget);

            if ($treeId > $spaceTarget) {
                $node->setTreeId($treeId + 1);
            }

            $this->_makeChildRootNode($node, $newTreeId);
        } else {
            if ($position === self::POSITION_LEFT) {
                if ($targetTreeId > $treeId) {
                    $leftSibling = $target->getPreviousSibling();

                    if ($node->pk === $leftSibling->pk) {
                        return;
                    }

                    $newTreeId = $leftSibling->getTreeId();
                    $lowerBound = $treeId;
                    $upperBound = $newTreeId;
                    $shift = -1;
                } else {
                    $newTreeId = $targetTreeId;

                    $lowerBound = $newTreeId;
                    $upperBound = $treeId;
                    $shift = 1;
                }
            } elseif ($position === self::POSITION_RIGHT) {
                if ($targetTreeId > $treeId) {
                    $newTreeId = $targetTreeId;
                    $lowerBound = $treeId;
                    $upperBound = $newTreeId;
                    $shift = -1;
                } else {
                    $rightSibling = $target->getNextSibling();

                    if ($node->pk === $rightSibling->pk) {
                        return;
                    }

                    $newTreeId = $rightSibling->getTreeId();
                    $lowerBound = $newTreeId;
                    $upperBound = $treeId;
                    $shift = 1;
                }
            } else {
                throw new Centurion_Db_Exception(sprintf('Invalid position was given %s.', $position));
            }

            $this->query($this->_getRootSiblingQuery($treeId, $newTreeId, $shift, $lowerBound, $upperBound));

            $node->setTreeId($newTreeId);
        }

        return $this;
    }

    /**
     * Manages spaces in the tree identified by treeId by changing the values of the left and right columns by size
     * after the given target point.
     *
     * @param int $size Size for updating the space
     * @param int $target The target point
     * @param int $treeId The tree identified
     * @return $this
     */
    protected function _manageSpace($size, $target, $treeId)
    {
        $this->query($this->_getSpaceQuery($target, $size, $treeId));

        return $this;
    }

    /**
     * Calls the appropriate method to move child node node relative to the given target node as specified by position.
     *
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $node The node object
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $target The target node object
     * @param string $position Valid values are first-child, last-child, left or right
     * @return Core_Traits_Mptt_Model_DbTable
     */
    protected function _moveChildNode($node, $target, $position)
    {
        $treeId = $node->getTreeId();
        $targetTreeId = $target->getTreeId();

        if ($treeId === $targetTreeId) {
            return $this->_moveChildWithinTree($node, $target, $position);
        }

        return $this->_moveChildToNewTree($node, $target, $position);
    }

    /**
     * Moves child node node to a different tree, inserting it relative to the given target node in the new tree as specified by position.
     *
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $node The node object
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $target The target node object
     * @param string $position Valid values are first-child, last-child, left or right
     * @return $this
     */
    protected function _moveChildToNewTree($node, $target, $position)
    {
        $left = $node->getLeft();
        $right = $node->getRight();
        $level = $node->getLevel();
        $targetLeft = $target->getLeft();
        $targetRight = $target->getRight();
        $targetLevel = $target->getLevel();
        $treeId = $node->getTreeId();
        $newTreeId = $target->getTreeId();

        list($spaceTarget, $levelChange, $leftRightChange, $parent) = $this->_calculateInterTreeMoveValues($node, $target, $position);

        $treeWidth = $right - $left + 1;

        $this->_createSpace($treeWidth, $spaceTarget, $newTreeId);

        $this->_interTreeMoveAndCloseGap($node, $levelChange, $leftRightChange, $newTreeId, $parent->pk);

        $node->setLeft($left - $leftRightChange);
        $node->setRight($right - $leftRightChange);
        $node->setLevel($level - $levelChange);
        $node->setTreeId($newTreeId);
        $node->setParentId($parent->pk);

        return $this;
    }

    /**
     *  Moves child node node within its current tree relative to the given target node as specified by position.
     *
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $node The node object
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $target The target node object
     * @param string $position Valid values are first-child, last-child, left or right
     * @return $this
     */
    protected function _moveChildWithinTree($node, $target, $position)
    {
        $left = $node->getLeft();
        $right = $node->getRight();
        $level = $node->getLevel();
        $width = $right - $left + 1;
        $treeId = $node->getTreeId();

        $targetLeft = $target->getLeft();
        $targetRight = $target->getRight();
        $targetLevel = $target->getLevel();

        if (in_array($position, array(self::POSITION_LAST_CHILD, self::POSITION_FIRST_CHILD))) {
            if ($node == $target)
                throw new Centurion_Db_Exception(sprintf('A node may not be made a child of itself.'));
            elseif ($left < $targetLeft && $targetLeft < $right)
                throw new Centurion_Db_Exception(sprintf('A node may not be made a child of any of its descendants.'));

            if ($position === self::POSITION_LAST_CHILD) {
                if ($targetRight > $right) {
                    $newLeft = $targetRight - $width;
                    $newRight = $targetRight - 1;
                } else {
                    $newLeft = $targetRight;
                    $newRight = $targetRight + $width - 1;
                }
            } else {
                if ($targetLeft > $left) {
                    $newLeft = $targetLeft - $width + 1;
                    $newRight = $targetLeft;
                } else {
                    $newLeft = $targetLeft + 1;
                    $newRight = $targetLeft + $width;
                }
            }

            $levelChange = $level - $targetLevel - 1;
            $parent = $target;
        } elseif (in_array($position, array(self::POSITION_LEFT, self::POSITION_RIGHT))) {
            if ($node == $target)
                throw new Centurion_Db_Exception(sprintf('A node may not be made a child of itself.'));
            elseif ($left < $targetLeft && $targetLeft < $right)
                throw new Centurion_Db_Exception(sprintf('A node may not be made a child of any of its descendants.'));

            if ($position === self::POSITION_LEFT ) {
                if ($targetLeft > $left) {
                    $newLeft = $targetLeft - $width;
                    $newRight = $targetLeft - 1;
                } else {
                    $newLeft = $targetLeft;
                    $newRight = $targetLeft + $width - 1;
                }
            } else {
                if ($targetRight > $right) {
                    $newLeft = $targetRight - $width + 1;
                    $newRight = $targetRight;
                } else {
                    $newLeft = $targetRight + 1;
                    $newRight = $targetRight + $width;
                }
            }

            $levelChange = $level - $targetLevel;
            $parent = $target->getParent();
        } else {
            throw new Centurion_Db_Exception(sprintf('An invalid position was given: %s.', position));
        }

        $leftBoundary = min(array($left, $newLeft));
        $rightBoundary = max(array($right, $newRight));

        $leftRightChange = $newLeft - $left;

        $gapSize = $width;

        if ($leftRightChange > 0) {
            $gapSize = -$gapSize;
        }

        $result = $this->query($this->_getMoveSubtreeQuery($left, $right, $levelChange, $leftRightChange,
                                                                         $leftBoundary, $rightBoundary, $gapSize,
                                                                         $node, $parent, $treeId));

        $node->setLeft($newLeft);
        $node->setRight($newRight);
        $node->setLevel($level - $levelChange);
        $node->setParentId($parent->id);

        return $this;
    }

    /**
     * Moves root node to a different tree, inserting it relative to the given target node as specified by position.
     *
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $node The node object
     * @param Core_Traits_Mptt_Model_DbTable_Row_Interface $target The target node object
     * @param string $position Valid values are first-child, last-child, left or right
     * @return $this
     */
    protected function _moveRootNode($node, $target, $position)
    {
        $left = $node->getLeft();
        $right = $node->getRight();
        $level = $node->getLevel();
        $treeId = $node->getTreeId();
        $newTreeId = $target->getTreeId();

        $width = $right - $left + 1;

        list($spaceTarget, $levelChange, $leftRightChange, $parent) = $this->_calculateInterTreeMoveValues($node, $target, $position);

        $this->_createSpace($width, $spaceTarget, $newTreeId);

        $this->query($this->_getMoveTreeQuery($levelChange, $leftRightChange, $node,
                                                            $parent, $left, $right, $treeId, $newTreeId));

        $node->setLeft($left - $leftRightChange);
        $node->setRight($right - $leftRightChange);
        $node->setLevel($level - $levelChange);
        $node->setTreeId($newTreeId);
        $node->setParentId($parent->pk);

        return $this;
    }

    protected function _getMoveTreeQuery($levelChange, $leftRightChange, $node, $parent, $left, $right, $treeId, $newTreeId)
    {
        $moveTreeQuery = <<<EOF
UPDATE {$this->_name}
SET %level% = %level% - %s,
    %left% = %left% - %s,
    %right% = %right% - %s,
    %tree% = %s,
    %parentColumn% = CASE
        WHEN %pk% = %s
            THEN %s
        ELSE %parentColumn% END
WHERE %left% >= %s AND %left% <= %s
  AND %tree% = %s
EOF;

        return sprintf($this->_attachVariables($moveTreeQuery),
                       $levelChange, $leftRightChange, $leftRightChange,
                       $newTreeId, $node->pk, $parent->pk, $left, $right, $treeId);
    }

    protected function _getMoveSubtreeQuery($left, $right, $levelChange, $leftRightChange, $leftBoundary,
        $rightBoundary, $gapSize, $node, $parent, $treeId
    ) {
        $moveSubtreeQuery = <<<EOF
UPDATE {$this->_name}
SET %level% = CASE
        WHEN %left% >= %s AND %left% <= %s
          THEN %level% - %s
        ELSE %level% END,
    %left% = CASE
        WHEN %left% >= %s AND %left% <= %s
          THEN %left% + %s
        WHEN %left% >= %s AND %left% <= %s
          THEN %left% + %s
        ELSE %left% END,
    %right% = CASE
        WHEN %right% >= %s AND %right% <= %s
          THEN %right% + %s
        WHEN %right% >= %s AND %right% <= %s
          THEN %right% + %s
        ELSE %right% END,
    %parentColumn% = CASE
        WHEN %pk% = %s
          THEN %s
        ELSE %parentColumn% END
WHERE %tree% = %s
EOF;

        return sprintf($this->_attachVariables($moveSubtreeQuery),
                       $left, $right, $levelChange,
                       $left, $right, $leftRightChange,
                       $leftBoundary, $rightBoundary, $gapSize,
                       $left, $right, $leftRightChange,
                       $leftBoundary, $rightBoundary, $gapSize,
                       $node->pk, $parent->pk, $treeId);
    }

    protected function _getSpaceQuery($target, $size, $treeId)
    {
        $spaceQuery = <<<EOF
UPDATE {$this->_name}
SET %left% = CASE
        WHEN %left% > %s
            THEN %left% + %s
        ELSE %left% END,
    %right% = CASE
        WHEN %right% > %s
            THEN %right% + %s
        ELSE %right% END
WHERE %tree% = %s
  AND (%left% > %s OR %right% > %s)
EOF;

        return sprintf($this->_attachVariables($spaceQuery),
                       $target, $size, $target, $size, $treeId, $target, $target);
    }

    protected function _getRootSiblingQuery($treeId, $newTreeId, $shift, $lowerBound, $upperBound)
    {
        $rootSiblingQuery = <<<EOF
UPDATE {$this->_name}
SET %tree% = CASE
    WHEN %tree% = %s
        THEN %s
    ELSE %tree% + %s END
WHERE %tree% >= %s AND %tree% <= %s
EOF;

        return sprintf($this->_attachVariables($rootSiblingQuery),
                       $treeId, $newTreeId, $shift,
                       $lowerBound, $upperBound);
    }

    protected function _getInterTreeMoveQuery($left, $right, $levelChange, $newTreeId,
        $leftRightChange, $gapTargetLeft, $gapSize, $node, $parentPk = null
    ) {
        $interTreeMoveQuery = <<<EOF
UPDATE {$this->_name}
SET %level% = CASE
        WHEN %left% >= %s AND %left% <= %s
            THEN %level% - %s
        ELSE %level% END,
    %tree% = CASE
        WHEN %left% >= %s AND %left% <= %s
            THEN %s
        ELSE %tree% END,
    %left% = CASE
        WHEN %left% >= %s AND %left% <= %s
            THEN %left% - %s
        WHEN %left% > %s
            THEN %left% - %s
        ELSE %left% END,
    %right% = CASE
        WHEN %right% >= %s AND %right% <= %s
            THEN %right% - %s
        WHEN %right% > %s
            THEN %right% - %s
        ELSE %right% END,
    %parentColumn% = CASE
        WHEN %pk% = %s
            THEN %s
        ELSE %parentColumn% END
WHERE %tree% = %s
EOF;

        return sprintf($this->_attachVariables($interTreeMoveQuery),
                       $left, $right, $levelChange,
                       $left, $right, $newTreeId,
                       $left, $right, $leftRightChange,
                       $gapTargetLeft, $gapSize,
                       $left, $right, $leftRightChange,
                       $gapTargetLeft, $gapSize,
                       $node->pk, null === $parentPk ? 'NULL' : $parentPk, $node->getTreeId());
    }

    /**
     * Attach variables to a query.
     *
     * @param string $string The query
     * @return string The query with attached variables
     */
    protected function _attachVariables($string)
    {
        return str_replace(array_keys($this->_variables), $this->_variables, $string);
    }

    public function getLimitDepthSelect($maxDepth, $select = null)
    {
        if (null == $select)
            $select = $this->select(true);

        $select->where(sprintf('%s.%s <= ?', $this->_modelInfo[Centurion_Db_Table_Abstract::NAME], self::MPTT_LEVEL), ($maxDepth - 1));

        return $select;
    }
}
