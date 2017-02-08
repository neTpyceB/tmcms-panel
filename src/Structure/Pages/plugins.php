<?php

use TMCms\Files\FileSystem;
use TMCms\Files\Finder;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTableHelper;

defined('INC') or exit;

$data = [];

foreach (Finder::getInstance()->getPathFolders(Finder::TYPE_PLUGINS) as $folder) {
    $folder = DIR_BASE . $folder;
    // Create dir if not found already
    FileSystem::mkDir($folder);

    $cms_plugin_files = array_diff(scandir($folder), ['.', '..']);
    foreach ($cms_plugin_files as $k => $v) {
        $data[$v] = [
            'filename' => str_replace('plugin.php', '', $v),
            'type' => basename($folder)
        ];
    }
}

BreadCrumbs::getInstance()
    ->addCrumb(ucfirst(P))
    ->addCrumb('All Plugins')
;

echo CmsTableHelper::outputTable([
    'data' => $data,
    'columns' => [
        'filename',
        'type',
    ],
]);