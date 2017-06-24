<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageAliasEntityRepository;
use TMCms\Log\App;
use TMCms\Routing\Structure;

defined('INC') or exit;

if (!isset($_GET['name'])) {
    return;
}

$quick_link = new PageAliasEntityRepository();
$quick_link->setWhereName($_GET['name']);
$quick_link->deleteObjectCollection();

App::add('Alias "' . $_GET['name'] . '" deleted');
Messages::sendGreenAlert('Alias "' . $_GET['name'] . '" deleted');

Structure::clearCache();
back();