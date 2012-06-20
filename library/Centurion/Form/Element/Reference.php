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
 * @package     Centurion_Form
 * @subpackage  Element
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Form
 * @subpackage  Element
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Nicolas Duteil <nd@octaveoctave.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Form_Element_Reference extends Centurion_Form_Element_Info
{
    /**
     * The row
     * @var string Class name of the reference table.
     */
    protected $_reference = null;

    /**
     * Get the row referenced by the value and set it to the element before rendering it.
     * @param Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if ($this->getValue() !== null) {
            $relatedModel = Centurion_Db::getSingletonByClassName($this->_reference);
            $this->setValue($relatedModel->findOneById($this->getValue()));
        }

        return parent::render($view);
    }

    /**
     * Setter for $this->_reference
     *
     * @param Centurion_Db_Table_Row_Abstract $reference
     * @return Centurion_Form_Element_Reference
     */
    public function setReference($reference)
    {
        $this->_reference = $reference;

        return $this;
    }
}
