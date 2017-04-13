<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Users\Entity\AdminUserGroup;
use TMCms\Admin\Users\Entity\GroupAccess;
use TMCms\Admin\Users\Entity\GroupAccessRepository;
use TMCms\Log\App;

defined('INC') or exit;

if (!isset($_GET['id'])) {
    return;
}

$id = abs((int)$_GET['id']);
if (!$id) {
    return;
}

// Admin group always have access to any action
if ($id == 1) {
    $_POST['all_permissions'] = 1;
}

// Clear existing access
$permissions = new GroupAccessRepository();
$permissions->setWhereGroupId($id);
$permissions->deleteObjectCollection();

// Set full access if provided
$group = new AdminUserGroup($id);
$group->setFullAccess((int)isset($_POST['all_permissions']));
$group->save();

// Skip other permission if have full access
if (isset($_POST['all_permissions'])) {
    go('?p=' . P . '&do=groups&highlight='. $group->getId());
}

// Save permission by action
$_POST['home']['_default'] = 1; // Anyone may see log-in page
$_POST['home']['_exit'] = 1; // Anyone may log out

// Create entries
foreach ($_POST as $k => $v) {
    foreach ($v as $key => $val) {
        $permission = new GroupAccess();
        $permission->setGroupId($id);
        $permission->setP($k);
        $permission->setDo($key);
        $permission->save();
    }
}

App::add('Permissions for group "' . $group->getTitle() . '" updated');
Messages::sendGreenAlert('Permissions updated');

go('?p=' . P . '&do=groups&highlight='. $id);