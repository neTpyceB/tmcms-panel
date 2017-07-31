<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Users\Entity\AdminUserGroup;
use TMCms\Log\App;

$id = abs((int)$_GET['id']);
if (!$id) {
    return;
}

// Can not edit main admin group
if (USER_ID !== 1 && $id === 1) {
    back();
}

$group = new AdminUserGroup($id);
$group->loadDataFromArray($_POST);
$group->setCanSetPermissions((int)isset($_POST['can_set_permissions']));
$group->setFilemanagerLimited((int)isset($_POST['filemanager_limited']));
$group->save();

App::add('Group "' . $group->getTitle() . '" edited');
Messages::sendGreenAlert('Group updated');

go('?p=' . P . '&do=groups&highlight=' . $group->getId());