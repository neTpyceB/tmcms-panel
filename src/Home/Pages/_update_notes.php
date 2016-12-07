<?php

defined('INC') or exit;

use TMCms\Admin\Messages;
use TMCms\Admin\Users\Entity\AdminUser;
use TMCms\Log\App;

$user = new AdminUser(USER_ID);
$user->loadDataFromArray($_POST);
$user->save();

App::add('Notes updated');

Messages::sendGreenAlert('Notes updated');

back();