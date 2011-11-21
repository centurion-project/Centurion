<?php

class Core_NavigationController extends Centurion_Controller_Action
{
    
    protected $_fix = true;
    
    public function checkNavigationAction()
    {
        $roots = Centurion_Db::getSingleton('core/navigation')->getRootNodes()->fetchAll();
        foreach ($roots as $node) {
            $this->_recursiveCheckLeftRight($node);
        }
        die();
    }
    
    protected function _debugNode($node)
    {
         echo 'Node id: ' . $node->id . '<br />';
         echo 'Left: ' . $node->getLeft() . '<br />';
         echo 'Right: ' . $node->getRight() . '<br />';
         echo 'Parent id: ' . $node->mptt_parent_id . '<br />';
         echo 'Tree id: ' . $node->mptt_tree_id . '<br />';
         echo 'Child count: ' . $node->getDescendantCount() . ' - ' . count($node->getDescendants()) . '<br />';
         echo '<br />';
    }
    
    protected function _checkTree($node)
    {
        if ($node->getParent() !== null && $node->mptt_tree_id !== $node->getParent()->mptt_tree_id) {
            echo 'Wrong tree id: <br />';
            $this->_debugNode($node);
            
            if ($this->_fix) {
                $node->setReadOnly(false);
                $node->setParentId($node->getParent()->mptt_tree_id);
                $node->save();
            }
        }
    }
    
    protected function _checkDescendant($node)
    {
        if ($node->getDescendantCount() != count($node->getDescendants())) {
            echo 'Wrong Descendant count: <br />';
            $this->_debugNode($node);
            
            if (count($node->getDescendants()) == 0) {
                if ($this->_fix) {
                    $node->setReadOnly(false);
                    $node->setRight($node->getLeft() + 1);
                    $node->save();
                }
            }
        }
    }
    
    protected function _recursiveCheckLeftRight($node)
    {
        $this->_checkDescendant($node);
        $this->_checkTree($node);
        
        $previous = $node->getLeft();
        foreach ($node->getChildren() as $nodeRow) {
            if ($nodeRow->getLeft() != ($previous + 1)) {
                echo sprintf('Not following previous (%s - %s): <br />', $previous + 1, $nodeRow->getLeft());
                $this->_debugNode($nodeRow);
                if ($this->_fix) {
                    $nodeRow->setReadOnly(false);
                    $nodeRow->setLeft($previous + 1);
                    $nodeRow->save();
                }
            }
            $this->_recursiveCheckLeftRight($nodeRow);
            $previous = $nodeRow->getRight();
        }
        
        if ($previous + 1 != $node->getRight()) {
            echo sprintf('Not following previous (%s - %s): <br />', $previous + 1, $node->getRight());
            $this->_debugNode($node);
            if ($this->_fix) {
                $node->setReadOnly(false);
                $node->setRight($previous + 1);
                $node->save();
            }
        }
    }
}