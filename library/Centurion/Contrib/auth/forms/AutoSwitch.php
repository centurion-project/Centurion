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
 * @package     Centurion_Contrib
 * @subpackage  Auth
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Auth
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Auth_Form_AutoSwitch extends Centurion_Form
{
    /**
     * Constructor.
     *
     * @param string $value Value for the checked element
     * @return void
     */
    public function __construct($value = null)
    {
        parent::__construct();

        $this->addElements(array(
            array('hidden', 'permission_id'),
            array('hidden', 'group_id'),
            array('checkbox', 'switch'),
            array('submit', 'submit')
        ));

        if (!is_null($value)) {
            $this->getElement('switch')->setChecked($value);
        }

        foreach ($this->getElements() as $element) {
            $element->removeDecorator('Label');
            $element->removeDecorator('Description');
            $element->removeDecorator('HtmlTag');
        }

        $this->getElement('submit')->removeDecorator('DtDdWrapper');

        $this->addDecorator('HtmlTag', array('tag' => 'div'));
    }

    /**
     * Save the form and the group-permission instance attached.
     *
     * @return Auth_Model_DbTable_Row_GroupPermission|int The Auth_Model_DbTable_Row_GroupPermission instance if saved, otherwise, the number of rows affected
     */
    public function save()
    {
        $groupPermissionTable = Centurion_Db::getSingleton('auth/group_permission');

        $groupId = $this->getElement('group_id')->getValue();
        $permissionId = $this->getElement('permission_id')->getValue();
        if (!$this->getElement('switch')->isChecked()) {
            $groupPermissionRow = $groupPermissionTable->findOneByGroupIdAndPermissionId($groupId, $permissionId);

            if ($groupPermissionRow instanceof Auth_Model_DbTable_Row_GroupPermission) {
                return $groupPermissionRow->delete();
            }
        } else {
            $groupPermissionRow = $groupPermissionTable->createRow();
            $groupPermissionRow->group_id = $groupId;
            $groupPermissionRow->permission_id = $permissionId;
            $groupPermissionRow->save();

            return $groupPermissionRow;
        }
    }
}