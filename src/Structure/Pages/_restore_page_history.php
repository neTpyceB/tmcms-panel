<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageComponentEntityRepository;
use TMCms\Log\App;
use TMCms\Routing\Structure;

defined('INC') or exit;

$page_id = (int)$_GET['id'];
$version = (int)$_GET['version'];

if (!$page_id || !$version) {
    return;
}

// Copy current to history
Structure::savePageComponentsToHistory($page_id);

// Delete current data
$components = new PageComponentEntityRepository();
$components->setWherePageId($page_id);
$components->deleteObjectCollection();

// Set versioned
q('INSERT INTO `cms_pages_components` (`page_id`, `component`, `data`) SELECT `page_id`, `component`, `data` FROM `cms_pages_components_history` WHERE `page_id` = "' . $page_id . '" AND `version` = "' . $version . '"');

App::add('Components on page "' . Structure::getPathById($page_id) . '" with id ' . $page_id . ' restored from version ' . $version);
Messages::sendGreenAlert('Components on page "' . Structure::getPathById($page_id) . '" restored');

back();