<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageComponentEntity;
use TMCms\Admin\Structure\Entity\PageComponentEntityRepository;
use TMCms\Admin\Structure\Entity\PageEntity;
use TMCms\Admin\Users;
use TMCms\Log\App;
use TMCms\Routing\Entity\PageComponentsCachedEntity;
use TMCms\Routing\Entity\PageComponentsCachedEntityRepository;
use TMCms\Routing\Entity\PageComponentsDisabledEntity;
use TMCms\Routing\Entity\PageComponentsDisabledEntityRepository;
use TMCms\Routing\Structure;

defined('INC') or exit;

$id = (int)$_GET['id'];

if (!$id) {
    return;
}

$page = new PageEntity($id);

if (!Users::getInstance()->checkSitePagePermissions($page->getId(), 'edit')) {
    error('You do not have permissions to edit page content.');
}

// Update page modification time for sitemap.xml
$page->setLastmodTs(NOW);
$page->save();

// Save to content history
Structure::savePageComponentsToHistory($page->getId());

// Set new values for usual components
$inserts = [];

// Delete all existing
$components = new PageComponentEntityRepository();
$components->setWherePageId($page->getId());
$components->deleteObjectCollection();

// Save values
foreach ($_POST as $k => $v) {
    // Disabled elements saved separately
    if ($k == 'disabled_elements') {
        continue;
    }
    // Cached elements saved separately
    if ($k == 'cached_elements') {
        continue;
    }
    // MySQL 5.7 have JSON tables and can save arrays
    if (is_array($v)) {
        $v = serialize($v);
    }

    $component = new PageComponentEntity();
    $component->setPageId($page->getId());
    $component->setComponent($k);
    $component->setData($v);
    $component->save();
}

// Delete all disabled components
$disabled_components = new PageComponentsDisabledEntityRepository();
$disabled_components->setWherePageId($page->getId());
$disabled_components->deleteObjectCollection();

// Save checked disabled elements
if (isset($_POST['disabled_elements']) && $_POST['disabled_elements']) {
    foreach ($_POST['disabled_elements'] as $k => $v) {
        $disabled_component = new PageComponentsDisabledEntity();
        $disabled_component->setPageId($page->getId());
        $disabled_component->setClass($k);
        $disabled_component->save();
    }
}

// Delete cached components
$cached_components = new PageComponentsCachedEntityRepository();
$cached_components->setWherePageId($page->getId());
$cached_components->deleteObjectCollection();

// Save checked cached elements
if (isset($_POST['cached_elements']) && $_POST['cached_elements']) {
    foreach ($_POST['cached_elements'] as $k => $v) {
        $cached_component = new PageComponentsCachedEntity();
        $cached_component->setPageId($page->getId());
        $cached_component->setClass($k);
        $cached_component->save();
    }
}

// Clear frontend cache
Structure::clearCache();

// Message to client
App::add('Components on page "' . Structure::getPathById($page->getId()) . '" with id ' . $page->getId() . ' edited');
Messages::sendGreenAlert('Components updated');

if (IS_AJAX_REQUEST) {
    die('1');
}

go('?p=' . P);