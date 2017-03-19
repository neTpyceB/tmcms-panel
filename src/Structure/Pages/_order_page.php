<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageEntity;
use TMCms\Config\Settings;
use TMCms\DB\SQL;
use TMCms\Log\App;
use TMCms\Routing\Structure;

defined('INC') or exit;

if (Settings::get('locked_structure')) {
    error('Site structure can not be modified.');
}

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    return;
}
$id = (int)$_GET['id'];
if (!$id) {
    return;
}

$page = new PageEntity($id);

SQL::orderCat($id, $page->getDbTableName(), $page->getPid(), 'pid', $_GET['direct']);

Structure::clearCache();

App::add('Page order changed');
Messages::sendGreenAlert('Page order updated');

back();