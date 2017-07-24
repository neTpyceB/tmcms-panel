<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Cache\Cacher;
use TMCms\Log\App;
use TMCms\Files\FileSystem;

defined('INC') or exit;

if (!class_exists('ZipArchive')) {
    error('Class ZipArchive is not installed');
}

ini_set('memory_limit', '1G');

$cache = Cacher::getInstance()->getDefaultCacher();

$micro_time = microtime(1);
ob_clean();

$files = FileSystem::scanDirs(DIR_BASE, true);
$so = count($files);

FileSystem::mkDir(DIR_TEMP);
$temp_file = DIR_TEMP . 'file_backup_' . NOW . '.zip';
$zip = new ZipArchive;
$zip->open($temp_file, ZipArchive::CREATE);

// Add files
for ($i = 0; $i < $so; $i++) {
    // Skip .git folder
    if (stripos($files[$i], '/.git/') !== false) continue;
    if (stripos($files[$i], '/.idea/') !== false) continue;

    $zip->addFile($files[$i], CFG_DOMAIN . substr($files[$i], strlen(DIR_BASE) - 1));
}

$zip->close();

$cache->set('cms_tools_last_files_backup_time', microtime(1) - $micro_time);

$data = $cache->get('cms_tools_disk_and_db_usage');

$cache->set('cms_tools_last_files_backup_size', $data['file_size']);

// Download file
FileSystem::streamOutput(CFG_DOMAIN . '_' . date('y-m-d') . '.zip', file_get_contents($temp_file), 'application/zip');

unlink($temp_file);

App::add('File backup created');
Messages::sendMessage('File backup created');

back();