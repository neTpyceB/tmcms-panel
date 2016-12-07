<?php

defined('INC') or exit;

use TMCms\Admin\Users;
use TMCms\Log\App;

Users::getInstance()->deleteSession($_SESSION['admin_id']);

App::add('User "' . $_SESSION['admin_login'] . '" logged out');

$_SESSION['admin_logged'] = false;
$_SESSION['admin_id'] = false;
$_SESSION['admin_login'] = false;
$_SESSION['admin_super'] = false;
$_SESSION['admin_sid'] = false;

go(DIR_CMS_URL);