<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Users;
use TMCms\Admin\Users\Entity\AdminUserGroup;
use TMCms\Admin\Users\Entity\AdminUserRepository;
use TMCms\Log\App;

$id = (int)$_GET['id'];
if (!$id) {
    return;
}

// Only main admin can delete main group
if (USER_ID !== 1 && $id == 1) {
    return;
}

$group = new AdminUserGroup($id);

// Can not delete own group
if (Users::getInstance()->getUserData('group_id') == $id || $group->getUndeletable()) {
    error('You can not delete this group');
}

$users_collections = new AdminUserRepository();
$users_collections->setWhereGroupId($id);

// Can not delete group with existing users
if ($users_collections->hasAnyObjectInCollection()) {
    error('There are some users in this group');
}

$group->deleteObject();

App::add('Group "' . $group->getTitle() . '" deleted');
Messages::sendGreenAlert('Group removed');

back();