<?php
class Core_Traits_Mptt_Model_DbTable_Row extends Centurion_Traits_Model_DbTable_Row_Abstract
{
    protected $_children = null;
    protected $_parent = null;

    public function init()
    {

        if (!class_implements($this->getTable(), 'Core_Traits_Mptt_Model_DbTable_Interface'))
            throw new Centurion_Traits_Exception(sprintf('Class %s defined as \'Table class\' for the %s row must implement Core_Traits_Mptt_Model_DbTable_Interface',
                                                          get_class($this->getTable()), get_class($this->_row)));

        Centurion_Signal::factory('pre_save')->connect(array($this, 'preSave'), $this->_row);
        Centurion_Signal::factory('pre_delete')->connect(array($this, 'preDelete'), $this->_row);
    }

    /**
     * Retrieve the value of the treeId attribute.
     *
     * @return int
     */
    public function getTreeId()
    {
        return $this->{Core_Traits_Mptt_Model_DbTable::MPTT_TREE};
    }

    /**
     * Set the value of the treeId attribute.
     *
     * @param int $treeId The new value of the treeId attribute
     * @return Centurion_Db_Table_Row_Mptt
     */
    public function setTreeId($treeId)
    {
        return $this->_setData(Core_Traits_Mptt_Model_DbTable::MPTT_TREE, $treeId);
    }

    /**
     * Retrieve the value of the left attribute.
     *
     * @return int
     */
    public function getLeft()
    {
        return $this->{Core_Traits_Mptt_Model_DbTable::MPTT_LEFT};
    }

    /**
     * Set the value of the left attribute.
     *
     * @param int $left The new value of the left attribute
     * @return Centurion_Db_Table_Row_Mptt
     */
    public function setLeft($left)
    {
        return $this->_setData(Core_Traits_Mptt_Model_DbTable::MPTT_LEFT, $left);
    }

    /**
     * Retrieve the value of the right attribute.
     *
     * @return int
     */
    public function getRight()
    {
        return $this->{Core_Traits_Mptt_Model_DbTable::MPTT_RIGHT};
    }

    /**
     * Set the value of the right attribute.
     *
     * @param int $right The new value of the right attribute
     * @return Centurion_Db_Table_Row_Mptt
     */
    public function setRight($right)
    {
        return $this->_setData(Core_Traits_Mptt_Model_DbTable::MPTT_RIGHT, $right);
    }

    /**
     * Retrieve the parent id with the parent column attribute.
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->{Core_Traits_Mptt_Model_DbTable::MPTT_PARENT};
    }

    /**
     * Set the value of the parent id.
     *
     * @param int $right The new value of the parent id
     * @return Centurion_Db_Table_Row_Mptt
     */
    public function setParentId($parentId)
    {
        return $this->_setData(Core_Traits_Mptt_Model_DbTable::MPTT_PARENT, $parentId);
    }

    /**
     * Retrieve the parent object.
     *
     * @return Centurion_Db_Table_Row_Mptt
     */
    public function getParent()
    {
        if (null === $this->_parent) {
            Centurion_Db_Table_Abstract::setFiltersStatus(false);

            //Not working because mptt_parent_id seems to not be updated all times
            //$return = $this->{$this->getTable()->getParentRefRule()};
            $select = $this->getAncestorsSelect();

            if ($select !== null)
                $this->_parent = $select->fetchRow();
            else
                $this->_parent = null;
            Centurion_Db_Table_Abstract::setFiltersStatus(true);
        }
        return $this->_parent;
    }

    /**
     * Retrieve the value of the level attribute.
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->{Core_Traits_Mptt_Model_DbTable::MPTT_LEVEL};
    }

    /**
     * Set the value of the level attribute.
     *
     * @param int $level The new value of the level attribute
     * @return Centurion_Db_Table_Row_Mptt
     */
    public function setLevel($level)
    {
        return $this->_setData(Core_Traits_Mptt_Model_DbTable::MPTT_LEVEL, $level);
    }

    //TODO: merge getPreviousSibling() and getNextSibling()

    /**
     * Returns this model instance's previous sibling in the tree, or null if it doesn't have a previous sibling.
     *
     * @return Centurion_Db_Table_Row_Mptt
     */
    public function getPreviousSibling()
    {
        $name = $this->getTable()->info('name');
        if ($this->isRootNode()) {
            $select = $this->getTable()->select(true, true)
                                       ->where(sprintf('%s.%s IS NULL', $name, Core_Traits_Mptt_Model_DbTable::MPTT_PARENT))
                                       ->where(sprintf('%s.%s < ?', $name, Core_Traits_Mptt_Model_DbTable::MPTT_TREE), $this->getTreeId())
                                       ->order(sprintf('%s.%s DESC', $name, Core_Traits_Mptt_Model_DbTable::MPTT_TREE));
        } else {
            $select = $this->getTable()->select(true, true)
                                       ->where(sprintf('%s.%s = ?', $name, Core_Traits_Mptt_Model_DbTable::MPTT_PARENT), $this->getParentId())
                                       ->where(sprintf('%s.%s < ?', $name, Core_Traits_Mptt_Model_DbTable::MPTT_RIGHT), $this->getLeft())
                                       ->order(sprintf('%s.%s DESC', $name, Core_Traits_Mptt_Model_DbTable::MPTT_RIGHT));
        }

        return $select->fetchRow();
    }

    /**
     * Returns this model instance's next sibling in the tree, or null if it doesn't have a next sibling.
     *
     * @return Centurion_Db_Table_Row_Mptt
     */
    public function getNextSibling()
    {
        $name = $this->getTable()->info('name');
        if ($this->isRootNode()) {
            $select = $this->getTable()->select(true, true)
                                       ->where(sprintf('%s.%s IS NULL', $name, Core_Traits_Mptt_Model_DbTable::MPTT_PARENT))
                                       ->where(sprintf('%s.%s > ?', $name, Core_Traits_Mptt_Model_DbTable::MPTT_TREE), $this->getTreeId())
                                       ->order(sprintf('%s.%s ASC', $name, Core_Traits_Mptt_Model_DbTable::MPTT_TREE));
        } else {
            $select = $this->getTable()->select(true, true)
                                       ->where(sprintf('%s.%s = ?', $name, Core_Traits_Mptt_Model_DbTable::MPTT_PARENT), $this->getParentId())
                                       ->where(sprintf('%s.%s > ?', $name, Core_Traits_Mptt_Model_DbTable::MPTT_LEFT), $this->getRight())
                                       ->order(sprintf('%s.%s ASC', $name, Core_Traits_Mptt_Model_DbTable::MPTT_LEFT));
        }

        return $select->fetchRow();
    }

    /**
     * Returns true if this model instance is a root node, false otherwise.
     *
     * @return bool
     */
    public function isRootNode()
    {
        return $this->getLeft() == 1;
        //return $this->getParentId() === null;
    }

    /**
     * Returns true if this model instance is a child node, false otherwise.
     *
     * @return bool
     */
    public function isChildNode()
    {
        return !$this->isRootNode();
    }

    /**
     * Convenience method for calling moveNode of the DbTable with this model instance.
     *
     * @param Centurion_Db_Table_Row_Mptt $target The target node object
     * @param string $position Valid values are first-child, last-child, left or right
     * @return Core_Traits_Mptt_Model_DbTable
     */
    public function moveTo($target = null, $position = null)
    {
        if (null === $position) {
            $position = Core_Traits_Mptt_Model_DbTable::POSITION_LAST_CHILD;
        }

        return $this->getTable()->moveNode($this, $target, $position);
    }

    /**
     * Convenience method for calling insertNode of the DbTable with this model instance.
     *
     * @param Centurion_Db_Table_Row_Mptt $target The target node object
     * @param string $position Valid values are first-child, last-child, left or right
     * @param bool $commit If commit is True, node's save() method will be called before it is returned
     * @return Core_Traits_Mptt_Model_DbTable
     */
    public function insertAt($target, $position = null, $commit = false)
    {
        if (null === $position) {
            $position = Core_Traits_Mptt_Model_DbTable::POSITION_LAST_CHILD;
        }

        return $this->getTable()->insertNode($this->_row, $target, $position, $commit);
    }

    /**
     * Creates a rowset containing the ancestors of this model instance.
     *
     * @param bool $ascending Passing true will reverse the ordering (immediate parent first, root ancestor last)
     * @return Centurion_Db_Table_Rowset_Abstract
     */
    public function getAncestors($ascending = false)
    {
        if ($this->isRootNode()) {
            return;
        }

        return $this->getAncestorsSelect($ascending)->fetchAll();
    }

    public function getAncestorsSelect($ascending = false, $includeItSelf = false)
    {
        if ($this->isRootNode()) {
            return;
        }
        $concat = null;
        if ($includeItSelf)
            $concat = 'e';

       return $this->getTable()->select(true)
                                   ->reset(Zend_Db_Table_Select::ORDER)
                                   ->filter(array(sprintf('%s__lt' . $concat, Core_Traits_Mptt_Model_DbTable::MPTT_LEFT)    =>  $this->getLeft(),
                                                  sprintf('%s__gt' . $concat, Core_Traits_Mptt_Model_DbTable::MPTT_RIGHT)   =>  $this->getRight(),
                                                  Core_Traits_Mptt_Model_DbTable::MPTT_TREE                       =>  $this->getTreeId()))
                                   ->order(sprintf('%s %s', Core_Traits_Mptt_Model_DbTable::MPTT_LEFT, $ascending ? 'ASC' : 'DESC'));
    }

    /**
     * Creates a rowset containing the immediate children of this model instance, in tree order.
     *
     * @param string $order Ordering the rowset by a specified column
     * @return Centurion_Db_Table_Rowset_Abstract
     */
    public function getChildren($order = null)
    {
//        if ($this->isLeafNode()) {
//            return;
//        }
//
//        $select = $this->getTable()->select(true)
//                                   ->filter(array(Core_Traits_Mptt_Model_DbTable::MPTT_PARENT =>  $this->pk));
//
//        if (null !== $order) {
//            $select->reset(Zend_Db_Table_Select::ORDER)
//                   ->order($order);
//        }
//
//        return $this->getTable()->all($select);
        if (null === $this->_children) {
            $this->_children = $this->getDescendants(false, 1, $order);
        }
        return $this->_children;
    }

    /**
     * Creates a select containing descendants of this model instance, in tree order.
     *
     * @param bool $includeSelf If true, the rowset will also include this model instance
     * @return Centurion_Db_Table_Rowset_Abstract
     */
    public function getDescendantsSelect($includeSelf = false, $depth = null, $order = null)
    {
        if (!$includeSelf && $this->isLeafNode()) {
            return;
        }

        if ($includeSelf) {
            $filters = array(sprintf('%s__range', Core_Traits_Mptt_Model_DbTable::MPTT_LEFT)  =>  array($this->getLeft(), $this->getRight()));
        } else {
            $filters = array(
                sprintf('%s__gt', Core_Traits_Mptt_Model_DbTable::MPTT_LEFT)  =>  $this->getLeft(),
                sprintf('%s__lt', Core_Traits_Mptt_Model_DbTable::MPTT_LEFT)  =>  $this->getRight(),
            );
        }

        $filters[Core_Traits_Mptt_Model_DbTable::MPTT_TREE] = $this->{Core_Traits_Mptt_Model_DbTable::MPTT_TREE};

        if (null !== $depth) {
            $filters[sprintf('%s__lt%s', Core_Traits_Mptt_Model_DbTable::MPTT_LEVEL, ($includeSelf ? 'e' : ''))] = $this->{Core_Traits_Mptt_Model_DbTable::MPTT_LEVEL} + ($depth + 1);
        }

        if (null == $order)
            $order = sprintf('%s ASC', Core_Traits_Mptt_Model_DbTable::MPTT_LEFT);

        $select = $this->getTable()->select(true);
        $select->filter($filters)
               ->order($order);

        return $select;
    }

    /**
     * Creates a rowset containing descendants of this model instance, in tree order.
     *
     * @param bool $includeSelf If true, the rowset will also include this model instance
     * @return Centurion_Db_Table_Rowset_Abstract
     */
    public function getDescendants($includeSelf = false, $depth = null, $order = null)
    {
        $select = $this->getDescendantsSelect($includeSelf, $depth, $order);
        if ($select === null)
            return null;
        return $select->fetchAll();
    }

    /**
     * Returns the root node of this model instance's tree.
     *
     * @return $this
     */
    public function getRoot()
    {
        if ($this->isRootNode()) {
            return $this;
        }

        $filters = array(
            Core_Traits_Mptt_Model_DbTable::MPTT_TREE              =>  $this->getTreeId(),
            sprintf('%s__isnull', Core_Traits_Mptt_Model_DbTable::MPTT_PARENT)    =>  true
        );

        return $this->getTable()->get($filters);
    }

    /**
     * Creates a rowset containing siblings of this model instance.
     * Root nodes are considered to be siblings of other root.
     *
     * @param bool $includeSelf If true, the rowset will also include this model instance
     * @param string $order Ordering the rowset by a specified column
     * @return Centurion_Db_Table_Rowset_Abstract
     */
    public function getSiblings($includeSelf = false, $order = null)
    {
        if ($this->isRootNode()) {
            $filters = array(sprintf('%s__isnull', Core_Traits_Mptt_Model_DbTable::MPTT_PARENT) => true);
        } else {
            $filters = array(Core_Traits_Mptt_Model_DbTable::MPTT_PARENT  =>  $this->getParentId());
        }

        $select = $this->getTable()->select(true)
                                   ->filter($filters);

        if (!$includeSelf) {
            $select->where(sprintf('%s != ?', Core_Traits_Mptt_Model_DbTable::MPTT_PARENT), $this->pk);
        }

        if (null !== $order) {
            $select->reset(Zend_Db_Table_Select::ORDER)
                   ->order($order);
        }

        return $this->getTable()->fetchAll($select);
    }

    /**
     * Returns true if this model instance is a leaf node (it has no children), false otherwise.
     *
     * @return bool
     */
    public function isLeafNode()
    {
        return $this->getDescendantCount() === 0;
    }

    /**
     * Returns the number of descendants this model instance has.
     *
     * @return int
     */
    public function getDescendantCount()
    {
        return ($this->getRight() - $this->getLeft() - 1) / 2;
    }

    public function preSave()
    {
        $parent = $this->getParent();
        if (!$this->pk) {
            $this->insertAt($parent, Core_Traits_Mptt_Model_DbTable::POSITION_LAST_CHILD);
        } else {
            $oldParent = $this->getTable()->find($this->_cleanData[Core_Traits_Mptt_Model_DbTable::MPTT_PARENT])
                                          ->current();
            if ($parent !== null &&((null === $oldParent) || ($oldParent->pk !== $parent->pk))) {
                if ($oldParent !== null)
                    $this->setParentId($oldParent->pk);
                else
                    $this->setParentId(null);


                $this->moveTo($parent, Core_Traits_Mptt_Model_DbTable::POSITION_LAST_CHILD);

                $this->setParentId($parent->pk);
            }
            if ($parent == null) {
                $this->moveTo(null, Core_Traits_Mptt_Model_DbTable::POSITION_FIRST_CHILD);
            }
        }
    }

    public function preDelete()
    {
        //Before deleting, we move the node to root position
        $this->_row->refresh();

        if ($this->_row->getTable()->isRecursiveDelete()) {
            $children = $this->getChildren();
            if(!empty($children)){
	            foreach ($children as $node) {
	                if ($node->id !== $this->id){
	                    $node->delete();
                    }
	            }
            }
        }

        $this->_row->refresh();
        $this->setReadOnly(false);
        $this->getTable()->moveNode($this->_row);
    }

    /**
     * Set a value with a columnName.
     *
     * @param string $columnName Column name
     * @param string $value Value
     * @return $this
     */
    protected function _setData($columnName, $value)
    {
        $data = $this->_data;
        $data[$columnName] = $value;
        $this->_data = $data;

        $modifiedFields = $this->_modifiedFields;
        $modifiedFields[$columnName] = true;
        $this->_modifiedFields = $modifiedFields;

        return $this;
    }
}
