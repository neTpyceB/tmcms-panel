<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use \TMCms\Log\App;
use TMCms\Files\FileSystem;

defined('INC') or exit;

$extensions = ['html', 'twig', 'php', 'css', 'scss', 'styl', 'js', 'ts', 'coffee'];
$inserts = [];
$dir_base_l = strlen(DIR_BASE);

// Clear existing snapshot data
q('TRUNCATE TABLE `cms_file_consistency`');

foreach ([DIR_FRONT, DIR_PUBLIC] as $scan_folder) {
    // Scan all files and save its' contents in database
    foreach (FileSystem::scanDirs($scan_folder) as $v) {
        // Skip folders
        if ($v['type'] == 'dir') {
            continue;
        }
        $ext = pathinfo($v['name'], PATHINFO_EXTENSION);
        if (!in_array($ext, $extensions)) {
            continue;
        }
        $v['file'] = substr($v['full'], $dir_base_l);

        // Insert in lo priority without blocking
        q('INSERT LOW_PRIORITY INTO `cms_file_consistency` (`file`, `hash`, `content`, `ts`) VALUES ("'. sql_prepare($v['file']) .'", "'. md5($v['file']) .'", "'. sql_prepare(file_get_contents($v['full'])) .'", "'. NOW .'")', false);
    }
}

App::add('File consistency snapshot taken');
Messages::sendMessage('File consistency snapshot taken');

back();