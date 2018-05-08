<?php
declare(strict_types=1);

use TMCms\Files\FileSystem;
use TMCms\Orm\TableStructure;

defined('INC') or exit;

if (!$_POST['module_name']) {
    error('Module name require');
}

$module_name = $_POST['module_name'];

if (!$_POST['entity_name']) {
    error('Entity name require');
}

$entity_name = $_POST['entity_name'];

if (!$_POST['field_type']) {
    error('Field type require');
}

$field_type = TableStructure::FIELD_TYPES_AVAILABLE[$_POST['field_type']];

if (!$_POST['field_name']) {
    error('Field name require');
}

$field_name = $_POST['field_name'];

$entity_file = DIR_MODULES . $module_name . '/Entity/' . $entity_name;

$content = file_get_contents($entity_file);

$position_of_field_array = $content;

$const_definition = "const FIELD_AMOUNT_IN_STOCK = 'amount_in_stock';";
$field_definition = "
            self::FIELD_CATEGORY_ID => [
                'type' => 'index',
            ],";

dump($position_of_field_array);

back();
