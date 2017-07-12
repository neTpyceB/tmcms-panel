<?php

use TMCms\Admin\Users\Entity\AdminUser;
use TMCms\HTML\BreadCrumbs;

defined('INC') or exit;

$id = abs((int)$_GET['id']);
if (!$id) {
    return;
}

$user = new AdminUser($id);
$data = $user->getAsArray();

// Unset sensitive data
unset($data['password']);

BreadCrumbs::getInstance()
    ->addCrumb(__('Edit User'))
    ->addCrumb($user->getLogin());

echo $this->__users_add_edit_form($data)
    ->setAction('?p=' . P . '&do=_users_edit&id=' . $id)
    ->setSubmitButton('Update');