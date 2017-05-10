<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\StructurePagePermissionEntity;
use TMCms\Admin\Structure\Entity\StructurePagePermissionEntityRepository;
use TMCms\Admin\Users\Entity\AdminUserGroup;

defined('INC') or exit;

$group = new AdminUserGroup($_POST['group_id']);
$group->setStructurePermissions((int)isset($_POST['full_access']));
$group->save();

$permissions = new StructurePagePermissionEntityRepository();
$permissions->setWhereGroupId($group->getId());
$permissions->deleteObjectCollection();

// If not full access than we should save separate access items
if (!$full_access) {
    // Remove unwanted fields
    unset($_POST['group_id'], $_POST['full_access']);

    // Save all permissions
    foreach ($_POST as $k => $v) {
        $permission = new StructurePagePermissionEntity();
        $permission->loadDataFromArray([
            'group_id'   => $group->getId(),
            'page_id'    => (int)$k,
            'edit'       => (int)isset($v['edit']),
            'properties' => (int)isset($v['properties']),
            'active'     => (int)isset($v['active']),
            'delete'     => (int)isset($v['delete']),
        ]);
        $permission->save();
    }
}

Messages::sendGreenAlert('Permissions updated');

go('?p=' . P);