<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\CmsStructure;
use TMCms\Config\Settings;
use TMCms\Log\App;
use TMCms\Routing\Structure;

defined('INC') or exit;

if (!isset($_POST['from_id'], $_POST['to_branch'])) {
    return;
}

$from_id = (int)$_POST['from_id'];
$to_id = Structure::getIdByPath($_POST['to_branch']);

if (!$from_id || ($_POST['to_branch'] && $to_id === NULL)) {
    error('Wrong path');
}

// Copy pages and all data
CmsStructure::getInstance()->copy_pages($from_id, $to_id);

App::add('Branch in Structure created, from page_id ' . $from_id . ' to page_id ' . $to_id);
Messages::sendGreenAlert('Branch in Structure created');

// Generate new sitemap.xml file
if (Settings::get('autogenerate_sitemap_xml')) {
    Structure::generateStructureXml();
}

go('?p=' . P);