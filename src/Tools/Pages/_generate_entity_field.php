<?php
declare(strict_types=1);

use TMCms\Files\FileSystem;
use TMCms\Orm\TableStructure;
use TMCms\Strings\Converter;

defined('INC') or exit;

if (!$_POST['module_name']) {
    error('Module name require');
}

$module_name = $_POST['module_name'];

if (!$_POST['entity_name']) {
    error('Entity name require');
}

$entity_name = $_POST['entity_name'];

$entity_class = str_replace('.php', '', $entity_name);

if (!$_POST['field_type']) {
    error('Field type require');
}

$field_type = TableStructure::FIELD_TYPES_AVAILABLE[$_POST['field_type']];

if (!$_POST['field_name']) {
    error('Field name require');
}

$field_name = $_POST['field_name'];

// Read file
$entity_file = DIR_MODULES . $module_name . '/Entity/' . $entity_name;
$content = file_get_contents($entity_file);

// Pepare field name
$db_field_name = strtolower(trim(str_replace(' ', '_', $field_name), '_ '));
$const_field_name = 'FIELD_' . strtoupper($db_field_name);

// Check and add constant for field
$const_definition = "\nconst " . $const_field_name . " = '" . $db_field_name . "';";
if (stripos($content, $const_definition) === false) {
    $search_beginning = 'class '. $entity_class . ' extends EntityRepository';
    $position_of_class_beginning = stripos($content, $search_beginning) + strlen($search_beginning) + 2;

    // Insert field const
    $content = substr_replace($content, $const_definition, $position_of_class_beginning, 0);

    // Write file and read to memory again
    file_put_contents($entity_file, $content);
    $content = file_get_contents($entity_file);
}

// Check and add constant for db array
$field_definition = "\nself::" . $const_field_name . " => [\n'type' => '" . $field_type . "',\n],";
if (stripos($content, $field_definition) === false) {
    $search_beginning = "'fields' => [";
    $position_of_field_array = stripos($content, $search_beginning) + strlen($search_beginning);

    // Insert field const
    $content = substr_replace($content, $field_definition, $position_of_field_array, 0);

    // Write file
    file_put_contents($entity_file, $content);
}

// TODO generate migration for field (use SQL class), and run migration

back();
