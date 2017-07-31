<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Users\Entity\AdminUserRepository;
use TMCms\Log\App;

$ids = explode(',', $_GET['ids']);
if (!$ids) {
    back();
}

$objects = new AdminUserRepository($ids);
$objects->exportAsSerializedData(true);

App::add('Users exported');
Messages::sendGreenAlert('Users exported');

back();