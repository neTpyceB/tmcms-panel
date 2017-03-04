<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageQuicklinkEntityRepository;
use TMCms\Log\App;
use TMCms\Routing\Structure;

defined('INC') or exit;

if (!isset($_GET['name'])) {
    return;
}

$quick_link = new PageQuicklinkEntityRepository();
$quick_link->setWhereName($_GET['name']);
$quick_link->deleteObjectCollection();

App::add('Quicklink "' . $_GET['name'] . '" deleted');
Messages::sendGreenAlert('Quicklink "' . $_GET['name'] . '" deleted');

Structure::clearCache();
back();