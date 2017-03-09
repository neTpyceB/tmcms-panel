<?php

use TMCms\Admin\Tools\Entity\FileConsistencyEntity;
use TMCms\Admin\Tools\Entity\FileConsistencyEntityRepository;
use TMCms\Files\FileSystem;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTable;
use TMCms\HTML\Cms\CmsTableHelper;
use TMCms\HTML\Cms\Column\ColumnData;
use TMCms\HTML\Cms\Columns;

defined('INC') or exit;

// Ensure db table exists
$consist = new FileConsistencyEntityRepository();

$breadcrumbs = BreadCrumbs::getInstance();
$breadcrumbs->addCrumb(__('File Consistency'));

// Data from DB
$snapshot = [];
$snapshoted_files = new FileConsistencyEntityRepository();
/** @var FileConsistencyEntity $snapshoted_file */
foreach ($snapshoted_files->getAsArrayOfObjectData() as $snapshoted_file) {
    $snapshot[$snapshoted_file['hash']] = $snapshoted_file;
}

$data = [];

// Main set-ups
$extensions = ['php', 'css', 'js', 'scss', 'ts'];
$dir_base_length = strlen(DIR_BASE);

// Check all files
foreach (FileSystem::scanDirs(DIR_BASE) as $file_data) {
    // Skips folders
    if ($file_data['type'] == 'dir') {
        continue;
    }

    // Skip non-required files
    $ext = pathinfo($file_data['name'], PATHINFO_EXTENSION);
    if (!in_array($ext, $extensions)) {
        continue;
    }

    $file_data['file'] = substr($file_data['full'], $dir_base_length);
    $file_data['hash'] = md5($file_data['file']);

    // If not in DB
    if (!isset($snapshot[$file_data['hash']])) {
        $file_data['status'] = '<span style="color: green">New file</span>';
        $file_data['reverse'] = '<a href="?p=' . P . '&do=_file_consistency_reverse&file=' . $file_data['file'] . '&type=remove_file">Remove</a>';
        $file_data['ts'] = filectime($file_data['full']);
        $file_data['snapshot'] = '<a href="?p=' . P . '&do=_file_consistency_reverse&file=' . $file_data['file'] . '&type=add_to_snapshot">Add to snapshot</a>';

        // If changed content
    } elseif (sql_prepare($snapshot[$file_data['hash']]['content']) != sql_prepare(file_get_contents($file_data['full']))) {
        $file_data['status'] = '<span style="color: orange">Changed</span>';
        $file_data['reverse'] = '<a href="?p=' . P . '&do=_file_consistency_reverse&hash=' . $file_data['hash'] . '&type=reverse_content">Reverse content</a>';
        $file_data['snapshot'] = '<a href="?p=' . P . '&do=_file_consistency_reverse&file=' . $file_data['file'] . '&type=update_in_snapshot">Update snapshot</a>';
        $file_data['ts'] = $snapshot[$file_data['hash']]['ts'];
        unset($snapshot[$file_data['hash']]);

    } else {
        unset($snapshot[$file_data['hash']]);
        continue;
    }

    $data[] = $file_data;
}


// Show actual status of every file
foreach ($snapshot as $k => $file) {
    $file['status'] = '<span style="color: red">Not found</span>';
    $file['reverse'] = '<a href="?p=' . P . '&do=_file_consistency_reverse&hash=' . $k . '&type=recreate_deleted">Create new file</a>';
    $file['snapshot'] = '<a href="?p=' . P . '&do=_file_consistency_reverse&hash=' . $k . '&type=remove_snapshot">Remove snapshot</a>';
    $data[] = $file;
}

// No changes
if (!$data) {
    echo 'Files are ok';
    return;
}

$breadcrumbs->addAction('Make system snapshot', '?p=' . P . '&do=_file_consistency_snapshot');

echo CmsTableHelper::outputTable([
    'data' => $data,
    'columns' => [
        'file' => [
            'cut_long_strings' => false,
        ],
        'ts' => [
            'type' => 'datetime',
        ],
        'status' => [
            'html' => true,
        ],
        'reverse' => [
            'html' => true,
            'title' => '<span style="color: red">Decline</span>',
        ],
        'snapshot' => [
            'html' => true,
            'title' => '<span style="color: green">Accept</span>',
        ],
    ],
    'pager' => false,
]);