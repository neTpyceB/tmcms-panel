<?php
declare(strict_types=1);

use TMCms\Admin\Entity\UsersSessionEntityRepository;
use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Strings\UID;

defined('INC') or exit;

if (!isset($_GET['sid'])) {
    return;
}

$sid = $_GET['sid'];

$sessions = new UsersSessionEntityRepository;
$sessions->setWhereSid($sid);
$sessions->deleteObjectCollection();

App::add('Admin User session "' . $sid . '" kicked');
Messages::sendGreenAlert('Admin User session kicked');

back();