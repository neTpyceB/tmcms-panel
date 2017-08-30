<?php
declare(strict_types=1);

use TMCms\Cache\Cacher;
use TMCms\Config\Configuration;
use TMCms\Config\Settings;
use TMCms\Files\FileSystem;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\Log\Errors;
use TMCms\Strings\Converter;

defined('INC') or exit;

set_time_limit(300);

$cache = Cacher::getInstance()->getDefaultCacher();

// Count file and folder disk statistics
$data = $cache->get('cms_tools_disk_and_db_usage');
if ($data === NULL || isset($_GET['force'])) {
    $file_size = $dir_count = $file_count = $db_size = 0;

    // File and dir count and size
    foreach (FileSystem::scanDirs(DIR_BASE) as $v) {
        if ($v['type'] === 'dir') {
            ++$dir_count;
        } else {
            $file_size += $v['fs'];
            ++$file_count;
        }
    }

    $conn_data = Configuration::getInstance()->get('db');
    $db = $conn_data['name'];

    // DB size
    $db_size = q_value('SELECT SUM(`data_length` + `index_length`) AS "size" FROM `information_schema`.TABLES WHERE `table_schema` = "' . $db . '" GROUP BY table_schema;');

    // Save
    $data = [
        'directories' => $dir_count,
        'files'       => $file_count,
        'file_size'   => $file_size,
        'db_size'     => $db_size,
    ];

    $cache->set('cms_tools_disk_and_db_usage', $data, 86400);

    if (isset($_GET['force'])) {
        back();
    }
}

$file_size_quota = Settings::get('file_size_quota');
if ($file_size_quota) {
    $file_size_quota = $file_size_quota * 1024 * 1024;
    if ($data['file_size'] > $file_size_quota) {
        Errors::sendErrorToDevelopers('Exceeded quota of ' . $file_size_quota . ' MB', 'Exceeded quota of ' . $file_size_quota . ' MB');
    }
}

// Files backup
$last_files_backup_time = $cache->get('cms_tools_last_files_backup_time');
$last_files_backup_size = $cache->get('cms_tools_last_files_backup_size');
if ($last_files_backup_time && $last_files_backup_size) {
    $new_time = $data['file_size'] * $last_files_backup_time / $last_files_backup_size;
}

// DB Backup
$last_db_backup_time = $cache->get('cms_tools_last_db_backup_time');
$last_db_backup_size = $cache->get('cms_tools_last_db_backup_size');
if ($last_db_backup_time && $last_db_backup_size) {
    $new_db_time = $data['db_size'] * $last_db_backup_time / $last_db_backup_size;
}

BreadCrumbs::getInstance()
    ->addCrumb('Filesystem stats and backup')
    ->addAction('Rescan', '?p=' . P . '&do=' . P_DO . '&force');

echo CmsFormHelper::outputForm([
    'fields' => [
        'directories'     => [
            'type'  => 'html',
            'value' => number_format($data['directories'], 0, '', ' '),
        ],
        'files'           => [
            'type'  => 'html',
            'value' => number_format($data['files'], 0, '', ' '),
        ],
        'file_size'       => [
            'type'  => 'html',
            'value' => Converter::formatDataSize($data['file_size']) . ($file_size_quota && $data['file_size'] > $file_size_quota ? ' <small style="color: red">(Soft quota is ' . Settings::get('file_size_quota') . ' MB)</small>' : ''),
        ],
        'database_size'   => [
            'type'  => 'html',
            'value' => Converter::formatDataSize($data['db_size']),
        ],
        'backup_files'    => [
            'type'  => 'html',
            'value' => '<a href="?p=' . P . '&do=backup_files">Download</a>' . (isset($new_time) ? ' (~' . round($new_time, 2) . ' seconds)' : ''),
        ],
        'backup_database' => [
            'type'  => 'html',
            'value' => '<a href="?p=' . P . '&do=backup_db">Download</a>' . (isset($new_db_time) ? ' (~' . round($new_db_time, 2) . ' seconds)' : ''),
        ],
    ],
]);