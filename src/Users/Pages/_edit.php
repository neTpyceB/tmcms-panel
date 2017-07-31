<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Users;
use TMCms\Admin\Users\Entity\AdminUser;
use TMCms\Log\App;
use TMCms\Strings\Verify;

$id = abs((int)$_GET['id']);
if (!$id) {
    return;
}

// Only main admin may edit own info
if (1 == $id && USER_ID != 1) {
    back();
}

$pass_oroginal = $_POST['password'];
// Hash password if supplied
if ($pass_oroginal) {
    $_POST['password'] = Users::getInstance()->generateHash($_POST['password']);
} else {
    unset($_POST['password']);
}

$user = new AdminUser($id);
$user->loadDataFromArray($_POST);

// Is Active
$user->setActive((bool)isset($_POST['active']));

// Check email
if ($user->getEmail() && !Verify::email($user->getEmail())) error('Wrong e-mail');

// Activate on server
if ($pass_oroginal) {
    $this->_activate_user($user, $pass_oroginal);
}

$user->save();

App::add('User "' . $user->getLogin() . '" edited');
Messages::sendGreenAlert('User updated');

go('?p=' . P . '&highlight=' . $user->getId());