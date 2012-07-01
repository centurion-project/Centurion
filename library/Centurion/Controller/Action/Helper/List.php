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
 * @package     Centurion_Controller
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Controller
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Controller_Action_Helper_List extends Zend_Controller_Action_Helper_Abstract
{
    public static $options = array(
        'isPaginated'       =>  false,
        'currentPageNumber' =>  null,
        'itemCountPerPage'  =>  null,
        'page'              =>  null,
        'objectName'        =>  'object',
        'extra'             =>  array(),
        'viewScript'        =>  null
    );

    /**
     * Generic list of objects.
     *
     * @param Centurion_Db_Select|array|rowSet $object
     * @param array $options Override default options
     */
    public function direct($object, array $options = array())
    {
        $options = array_merge(self::$options, $options);

        if (true === $options['isPaginated']) {
            if ($object instanceof Zend_Paginator_Adapter_Interface) {
                $adapter = $object;
            } elseif ($object instanceof Zend_Db_Table_Select) {
                $adapter = new Zend_Paginator_Adapter_DbTableSelect($object);
            } elseif ($object instanceof Zend_Db_Select) {
                $adapter = new Zend_Paginator_Adapter_DbSelect($object);
            } elseif ($object instanceof Iterator) {
                $adapter = new Zend_Paginator_Adapter_Iterator($object);
            } elseif (is_array($object)) {
                $adapter = new Zend_Paginator_Adapter_Array($object);
            } else {
                throw new Centurion_Exception(sprintf('Unsupported class %s', get_class($object)));
            }

            $paginator = new Zend_Paginator($adapter);

            if (null === $options['currentPageNumber']) {
                $options['currentPageNumber'] = $this->getRequest()->getParam('page', 1);
            }

            $paginator->setCurrentPageNumber($options['currentPageNumber']);
            $paginator->setItemCountPerPage($options['itemCountPerPage']);

            $params = array(
                'paginator' => $paginator,
            );
        } else {
            if ($object instanceof Centurion_Db_Table_Select) {
                $params['rowset'] = $object->fetchAll();
            } elseif (is_array($object) || $object instanceof Iterator) {
                $params['rowset'] = $object;
            } else {
                throw new Centurion_Exception(sprintf('Given object is not supported: %s', get_class($object)));
            }
        }

        foreach ($options['extra'] as $key => $value) {
            if (is_callable($value)) {
                $params[$key] = call_user_func($value);
            } else {
                $params[$key] = $value;
            }
        }

        if (null === $options['viewScript']) {
            $options['viewScript'] = sprintf('%s/%s_list.phtml', $this->getRequest()->getControllerName(),
                                                                 $options['objectName']);
        }

        return $this->getActionController()->renderToResponse($options['viewScript'], $params);
    }
}
