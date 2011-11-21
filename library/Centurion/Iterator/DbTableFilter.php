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
 * @package     Centurion_Iterator
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Iterator
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Iterator_DbTableFilter extends FilterIterator
{
    /**
     * Constructor.
     *
     * @param string $path The path to be iterated
     */
    public function __construct($path)
    {
        parent::__construct(new Centurion_Iterator_Directory($path));
    }

    /**
     * Which iterable items to accept or deny, required by FilterInterface
     *
     * @return boolean
     */
    public function accept()
    {
        return !$this->getInnerIterator()->isDot()
               && $this->getInnerIterator()->getExtension() === 'php';
    }
}