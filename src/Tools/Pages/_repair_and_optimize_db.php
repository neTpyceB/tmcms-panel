<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageComponentCustomEntityRepository;
use TMCms\Admin\Structure\Entity\PageComponentEntityRepository;
use TMCms\Admin\Structure\Entity\PageComponentHistoryRepository;
use TMCms\Admin\Structure\Entity\PageEntityRepository;
use TMCms\Cache\Cacher;
use TMCms\DB\SQL;
use TMCms\Log\App;
use TMCms\Routing\Entity\PageComponentsDisabledEntityRepository;

defined('INC') or exit;

// Remove unused page components, if exist for some reason
$pages = new PageEntityRepository();
$ids = $pages->getIds();
if ($ids) {
    $components = new PageComponentEntityRepository();
    $components->addWhereFieldNotIn('id', $ids);
    $components->deleteObjectCollection();

    $components_custom = new PageComponentCustomEntityRepository();
    $components_custom->addWhereFieldNotIn('id', $ids);
    $components_custom->deleteObjectCollection();

    $components_disabled = new PageComponentsDisabledEntityRepository();
    $components_disabled->addWhereFieldNotIn('id', $ids);
    $components_disabled->deleteObjectCollection();

    $components_history = new PageComponentHistoryRepository();
    $components_history->addWhereFieldNotIn('id', $ids);
    $components_history->deleteObjectCollection();
}
unset($ids);

// Repair and optimize all tables
$tables = SQL::getTables();
foreach (SQL::getTables() as $table) {
	q('REPAIR TABLE `'. $table .'`');
	q('OPTIMIZE TABLE `'. $table .'`');
}

// Save last optimize time
Cacher::getInstance()->getDefaultCacher()->set('cms_tools_repair_and_optimize_db', 1, 604800);

App::add('DB Tables repaired and optimized');
Messages::sendMessage('DB Tables repaired and optimized');

back();