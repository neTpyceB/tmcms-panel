<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Users;
use TMCms\Admin\Users\Entity\AdminUser;
use TMCms\Admin\Users\Entity\AdminUserRepository;
use TMCms\Log\App;
use TMCms\Strings\Verify;

// Hash password
$pass_oroginal = $_POST['password'];
$_POST['password'] = Users::getInstance()->generateHash($_POST['password']);

$user = new AdminUser;
$user->loadDataFromArray($_POST);

// Check email is correct
if ($user->getEmail() && !Verify::email($user->getEmail())) {
    error('Wrong e-mail');
}

// Check we have no user with same login
if (AdminUserRepository::findOneEntityByCriteria(['login' => $user->getLogin()])) {
    error('User with same login already exists');
}

// Activate on server
$this->_activate_user($user, $pass_oroginal);

// Create entry in system
$user->save();

App::add('User "' . $user->getLogin() . '" added');
Messages::sendGreenAlert('User added');

go('?p=' . P . '&highlight=' . $user->getId());