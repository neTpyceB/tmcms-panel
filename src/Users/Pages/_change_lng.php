<?php
declare(strict_types=1);

use TMCms\Admin\Users\Entity\AdminUser;

$user = new AdminUser(USER_ID);
$user->setLng($_GET['lng']);
$user->save();

back();