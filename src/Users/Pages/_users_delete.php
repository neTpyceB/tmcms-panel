<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Users;
use TMCms\Admin\Users\Entity\AdminUser;
use TMCms\Log\App;

defined('INC') or exit;

if (!isset($_GET['id'])) {
    return;
}

$id = abs((int)$_GET['id']);
if (USER_ID == $id) {
    error('You can not delete self');
}
if (1 == $id) {
    error('This user can not be deleted');
}

$user = new AdminUser($id);

if (Users::getInstance()->getGroupData('undeletable', $user->getGroupId())) {
    error('This user can not be deleted');
}

$user->deleteObject();

App::add('User "' . $user->getLogin() . '" deleted');
Messages::sendGreenAlert('User deleted');

back();