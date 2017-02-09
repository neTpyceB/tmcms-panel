<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Users\Entity\AdminUserRepository;
use TMCms\Log\App;

defined('INC') or exit;

if (!isset($_GET['ids'])) {
    return;
}

$ids = explode(',', $_GET['ids']);
if (!$ids) {
    back();
}

// Mat not disable main admin
if (in_array(1, $ids)) {
    back();
}

$users = new AdminUserRepository($ids);
$users->flipBoolValue('active');
$users->save();

App::add('Users updated');
Messages::sendGreenAlert('Users updated');

back();