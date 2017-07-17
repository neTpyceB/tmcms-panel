<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageAliasEntity;
use TMCms\Admin\Structure\Entity\PageAliasEntityRepository;
use TMCms\Log\App;
use TMCms\Routing\Structure;

defined('INC') or exit;
$_POST['name'] = str_replace('/', '', $_POST['name']);

if (!$_POST['name'] || !$_POST['href']) {
    back();
}

// Original alias
$alias = new PageAliasEntity($_GET['id']);

// Check names if changed
if ($_POST['name'] !== $alias->getName()) {
    $quick_links = new PageAliasEntityRepository();
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
$alias->setName($_POST['name']);
$alias->setHref($_POST['href']);
$alias->setPageId($linked_page_id);
$alias->setIsLanding((int)isset($_POST['is_landing']));
$alias->save();

Structure::clearCache();

App::add('Alias ' . $_POST['name'] . ' edited');
Messages::sendGreenAlert('Alias ' . $_POST['name'] . ' edited');

go('?p=' . P . '&do=aliases');