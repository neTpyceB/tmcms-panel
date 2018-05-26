<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\CmsStructure;
use TMCms\Admin\Structure\Entity\PageEntity;
use TMCms\Admin\Structure\Entity\PageEntityRepository;
use TMCms\Config\Settings;
use TMCms\DB\SQL;
use TMCms\Log\App;
use TMCms\Routing\Languages;
use TMCms\Routing\Structure;
use TMCms\Strings\UID;

if (Settings::get('locked_structure')) {
    error('Site structure can not be modified.');
}

if (!$_POST['location']) {
    $_POST['location'] = UID::text2uid($_POST['title'], 50);
}

$pid = (int)$_POST['pid'];
$location = $_POST['location'];
$location = str_replace(' ', '_', $location);
$redirect_url = $_POST['redirect_url'];

if (q_check('cms_pages', '`pid` = "' . $pid . '" AND `location` = "' . $location . '"')) {
    error('Page exists in this level');
}

$redirect_id = Structure::getIdByPath($redirect_url);
if ($redirect_id) {
    $redirect_url = $redirect_id;
}
unset($redirect_id);

// If label is not set - use location
if (!$_POST['string_label']) {
    $_POST['string_label'] = $_POST['location'];
}

$page = new PageEntity();
$page->loadDataFromArray([
    'template_id' => (int)$_POST['template_id'],
    'pid' => $pid,
    'location' => $location,
    'title' => $_POST['title'],
    'order' => SQL::getNextOrder($page->getDbTableName(), 'order', 'pid', $pid),
    'keywords' => $_POST['keywords'],
    'description' => $_POST['description'],
    'transparent_get' => (int)isset($_POST['transparent_get']),
    'string_label' => $_POST['string_label'],
    'menu_name' => $_POST['menu_name'],
    'redirect_url' => $redirect_url,
    'html_file' => $_POST['html_file'],
    'go_level_down' => (int)isset($_POST['go_level_down']),
    'lastmod_ts' => NOW,
]);
$page->save();

$id = $page->getId();

// Duplicate page for other languages if required
if ($pid && !empty($_POST['duplicates'])) {
    $pid_page = new PageEntity($pid);
    $pid_label = $pid_page->getStringLabel();
    if ($pid_label === LNG) {
        // Copy by language - because each language has not the same label string
        foreach (Languages::getPairs() as $lng => $lng_full) {
            if ($lng === LNG) {
                // Skip current - already created
                continue;
            }

            $lng_parent_id = Structure::getIdByPath($lng);
            if ($lng_parent_id) {
                // If not exists - create a new page
                if (!PageEntityRepository::findOneEntityByCriteria(['pid' => $lng_parent_id, 'location' => $page->getLocation()])) {
                    CmsStructure::getInstance()->copy_pages($id, $lng_parent_id);
                }
            }
        }
    } else {
        // Copy by same parent label string
        foreach ($_POST['duplicates'] as $lng => $flag) {
            $lng_parent_id = Structure::getIdByLabel($pid_label, $lng);
            if ($lng_parent_id) {
                CmsStructure::getInstance()->copy_pages($id, $lng_parent_id);
            }
        }
    }
}

Structure::clearCache();

App::add('Page "' . Structure::getPathById($id) . '" with id ' . $id . ' added');
Messages::sendGreenAlert('Page added');

if (Settings::get('autogenerate_sitemap_xml')) {
    Structure::generateStructureXml();
}

go('?p=' . P);
