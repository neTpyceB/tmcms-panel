<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Users\Entity\AdminUserRepository;
use TMCms\Log\App;

$ids = explode(',', $_GET['ids']);
if (!$ids) {
    back();
}

// Mat not delete main admin
if (in_array(1, $ids, true)) {
    back();
}

$users = new AdminUserRepository($ids);
$users->deleteObjectCollection();

App::add('Users deleted');
Messages::sendGreenAlert('Users deleted');

back();