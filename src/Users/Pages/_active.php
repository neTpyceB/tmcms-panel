<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Users;
use TMCms\Admin\Users\Entity\AdminUser;
use TMCms\Log\App;

$id = abs((int)$_GET['id']);

if (USER_ID == $id) {
    error('You can not deactivate self');
}

// Only main admin may edit own info
if (1 == $id && USER_ID != 1) {
    back();
}

$user = new AdminUser($id);

if (Users::getInstance()->getGroupData('undeletable', $user->getGroupId())) {
    error('This user can not be deactivated');
}

$user->flipBoolValue('active');

$user->save();

App::add('User "' . $user->getLogin() . '" ' . ($user->getActive() ? '' : 'de') . 'activated');
Messages::sendGreenAlert('User updated');

back();