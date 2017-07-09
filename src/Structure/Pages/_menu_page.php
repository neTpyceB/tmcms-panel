<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageEntity;
use TMCms\Config\Settings;
use TMCms\Log\App;
use TMCms\Routing\Structure;

if (Settings::get('locked_structure')) {
    error('Site structure can not be modified.');
}

$id = (int)$_GET['id'];
if (!$id) {
    return;
}

// Change active status
$page = new PageEntity($id);
$page->flipBoolValue('in_menu');
$page->save();

// Clear all pages cache
Structure::clearCache();

if (Settings::get('autogenerate_sitemap_xml')) {
    Structure::generateStructureXml();
}

App::add('Page "' . $page->getLocation() . '" updated');
Messages::sendGreenAlert('Page updated');

back();