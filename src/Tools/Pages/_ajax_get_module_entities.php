<?php
declare(strict_types=1);

$selected_module_name = $_GET['selected_module_name'];

$entities = [-1 => '---'];

$folder_to_scan = DIR_MODULES . $selected_module_name . '/Entity/';

if (file_exists($folder_to_scan)) {
    foreach (scandir($folder_to_scan, SCANDIR_SORT_NONE) as $file_name) {
        if (stripos($file_name, 'EntityRepository.php') !== false) {
            $entities[$file_name] = $file_name;
        }
    }
}

echo json_encode($entities, JSON_OBJECT_AS_ARRAY);

die;
