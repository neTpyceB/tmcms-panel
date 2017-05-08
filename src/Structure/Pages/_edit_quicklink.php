<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageQuicklinkEntityRepository;
use TMCms\Log\App;
use TMCms\Routing\Structure;

defined('INC') or exit;

// Original name
$name = $_GET['name'];
if (!$name) {
    return;
}

// New name
$_POST['name'] = str_replace('/', '', $_POST['name']);

if (!$_POST['name'] || !$_POST['href']) {
    back();
}

if ($_POST['name'] != $name) {
    $quick_links = new PageQuicklinkEntityRepository();
    $quick_links->setWhereName($_POST['name']);
    if ($quick_links->hasAnyObjectInCollection()) {
        error('Link name exists.');
    }
}

$linked_page_id = Structure::getIdByPath($_POST['href']);
if (!$linked_page_id) {
    error('Link page not found');
}

// Update existing link
$quick_links = new PageQuicklinkEntityRepository();
$quick_links->setWhereName($_POST['name']);
$quick_links->setName($_POST['name']);
$quick_links->setHref($_POST['href']);
$quick_links->setPageId($linked_page_id);
$quick_links->setSearchword((int)isset($_POST['searchword']));
$quick_links->save();

Structure::clearCache();

App::add('Quicklink ' . $_POST['name'] . ' edited');
Messages::sendGreenAlert('Quicklink ' . $_POST['name'] . ' edited');

go('?p=' . P . '&do=quicklinks');