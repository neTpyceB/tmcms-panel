<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Cache\Cacher;
use TMCms\DB\SQL;
use TMCms\Files\FileSystem;
use TMCms\Log\App;

$micro_time = microtime(true);
ini_set('memory_limit', '1G');
ob_clean();

$res = [];
foreach (SQL::getTables() AS $tbl) {
    $res[] = 'DROP TABLE IF EXISTS `' . $tbl . '`;';
    $res[] = SQL::getCreateTable($tbl) . ';';
    $fields = SQL::getFields($tbl);
    $rows = SQL::getRows($tbl);
    $res[] = SQL::makeInserts($tbl, $rows, $fields) . ';';
}

$cache = Cacher::getInstance()->getDefaultCacher();

// Save time for stats
$cache->set('cms_tools_last_db_backup_time', microtime(1) - $micro_time);

// Show size in stats
$data = $cache->get('cms_tools_disk_and_db_usage');
$cache->set('cms_tools_last_db_backup_size', $data['db_size']);

FileSystem::streamOutput(CFG_DOMAIN . '.' . date('y-m-d') . '.sql', implode("\n", $res), 'application/zip');

App::add('DB backup created');
Messages::sendMessage('DB backup created');

back();