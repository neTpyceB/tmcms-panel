<?php
declare(strict_types=1);

use TMCms\Files\FileSystem;

$selected_module_name = $_GET['selected_module_name'];

$entities = [];

$folder_to_scan = DIR_MODULES . $selected_module_name . '/Entity/';

// Create folder so we don't have errors
FileSystem::mkDir($folder_to_scan);

foreach (scandir($folder_to_scan, SCANDIR_SORT_NONE) as $file_name) {
    if (stripos($file_name, 'Entity.php') !== false) {
        $entities[$file_name] = $file_name;
    }
}

echo json_encode($entities, JSON_OBJECT_AS_ARRAY);

die;
