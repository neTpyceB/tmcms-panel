<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageEntity;
use TMCms\Admin\Structure\Entity\PageRedirectHistoryEntity;
use TMCms\Admin\Structure\Entity\PageRedirectHistoryEntityRepository;
use TMCms\Admin\Users;
use TMCms\Config\Settings;
use TMCms\DB\TableTree;
use TMCms\Log\App;
use TMCms\Routing\Structure;
use TMCms\Strings\UID;

defined('INC') or exit;

if (Settings::get('locked_structure')) {
    error('Site structure can not be modified.');
}

$id = (int)$_GET['id'];
if (!$id) {
    return;
}

$page = new PageEntity($id);

// Have permissions to edit page?
if (!Users::getInstance()->checkSitePagePermissions($page->getId(), 'properties')) {
    error('You do not have permissions to edit page properties.');
}

// Whether we should save location change later in code
$save_location_change_history = Settings::get('save_location_change_history');

// Auto generate location
if (!$_POST['location']) {
    $_POST['location'] = UID::text2uid($_POST['location'], 50);
}

$pid = (int)$_POST['pid'];
$location = str_replace(' ', '_', $_POST['location']);
$redirect_url = $_POST['redirect_url'];

// Moved to same level?
if (q_check('cms_pages', '`pid` = "' . $pid . '" AND `location` = "' . $location . '" && `id` != "' . $page->getId() . '"')) {
    error('Page exists in this level');
}

// Moved as self child?
foreach (TableTree::getInstance('cms_pages')->getFlowUp($pid) as $v) {
    if ($v['id'] == $page->getId()) {
        // Found self in leaf
        error('Parent page should not be under current page.');
    }
}

if ($redirect_url) {
    // If redirect url was edited via page selector and ID is in input - we make full URL from it
    $redirect_id = Structure::getIdByPath($redirect_url);
    if ($redirect_id) {
        $redirect_url = $redirect_id;
    }
}

// If page location is changed - save path change history
$old_location = '';
if ($page['location'] != $location && $save_location_change_history) {
    // Current old path
    $old_location = Structure::getPathById($page->getId(), false);
}

// Update page
unset($_POST['id']); // Remove sensitive data just in case

// Set overall data from previous page
$page->loadDataFromArray($_POST);

// Set special fields changed in process
$page->setPid($pid);
$page->setLocation($location);
$page->setTransparentGet((int)isset($_POST['transparent_get']));
$page->setGoLevelDown((int)isset($_POST['go_level_down']));
$page->setRedirectUrl($redirect_url);
$page->setLastmodTs(NOW);
$page->save();

Structure::clearCache();

App::add('Page "' . Structure::getPathById($page->getId()) . '" with id "' . $page->getId() . '" edited');
Messages::sendGreenAlert('Page updated');

// Generate xml file for search engines
if (Settings::get('autogenerate_sitemap_xml')) {
    Structure::generateStructureXml();
}

// Save change history
if ($old_location && $save_location_change_history) {
    // Make sure db exists
    new PageRedirectHistoryEntityRepository();

    $new_location = Structure::getPathById($page->getId(), false);

    if ($old_location != $new_location) {
        // Set as not last in history
        $redirect_history = new PageRedirectHistoryEntityRepository();
        $redirect_history->setWherePageId($page->getId());
        $redirect_history->setLast(0);
        $redirect_history->save();

        // Save redirect in history
        $redirect_history = new PageRedirectHistoryEntity();
        $redirect_history->loadDataFromArray([
            'page_id'      => $page->getId(),
            'old_full_url' => $old_location,
            'new_full_url' => $new_location,
            'last'         => 1,
        ]);
    }
}

go('?p=' . P);