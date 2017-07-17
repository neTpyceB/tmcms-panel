<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageEntityRepository;
use TMCms\Admin\Users;
use TMCms\Config\Settings;
use TMCms\DB\TableTree;
use TMCms\Log\App;
use TMCms\Routing\Structure;

if (Settings::get('locked_structure')) {
    error('Site structure can not be modified.');
}

$id = (int)$_GET['id'];
if (!$id) {
    return;
}

if (!Users::getInstance()->checkSitePagePermissions($id, 'delete')) {
    error('You do not have permissions to delete this page.');
}

// Add all subpages
$ids = [];
foreach (TableTree::getInstance('cms_pages')->getAsArray($id) as $k => $v) {
    $ids[] = $k;
}
unset($k, $v);
// Add own page
$ids[] = $id;
if (!$ids) {
    return;
}

// Remove from DB
$page = new PageEntityRepository($ids);
$page->deleteObjectCollection();

Structure::clearCache();

App::add('Page "' . Structure::getPathById($id) . '" with id ' . $id . ' deleted');
Messages::sendGreenAlert('Page deleted');

// Generate sitemap.xml for SEO
if (Settings::get('autogenerate_sitemap_xml')) {
    Structure::generateStructureXml();
}

back();