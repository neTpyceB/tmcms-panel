<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageEntity;
use TMCms\Admin\Users;
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


if (!Users::getInstance()->checkSitePagePermissions($id, 'active')) {
    error('You do not have permissions to activate/deactivate this page.');
}

$page = new PageEntity($id);
$page->flipBoolValue('active');
$page->save();

Structure::clearCache();

App::add('Page "' . $page->getLocation() . '" ' . ($page->getActive() ? '' : 'de') . 'activated');
Messages::sendGreenAlert('Page updated');

if (Settings::get('autogenerate_sitemap_xml')) {
    Structure::generateStructureXml();
}


back();