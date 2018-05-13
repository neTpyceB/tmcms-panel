<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Updater;
use TMCms\DB\Adapter\MySQL;
use TMCms\Log\App;
use TMCms\Orm\Entity;
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

$entity_repository_class = str_replace('.php', '', $entity_name);

if (!$_POST['field_type']) {
    error('Field type require');
}

$field_type = TableStructure::FIELD_TYPES_AVAILABLE[$_POST['field_type']];

if (!$_POST['field_name']) {
    error('Field name require');
}

$field_name = $_POST['field_name'];

// Read file
$entity_repository_file = DIR_MODULES . $module_name . '/Entity/' . $entity_name;

$content = file_get_contents($entity_repository_file);

// Pepare field name
$db_field_name = strtolower(trim(str_replace(' ', '_', $field_name), '_ '));
$const_field_name = 'FIELD_' . strtoupper($db_field_name);

// Check and add constant for field
$const_definition = "\nconst " . $const_field_name . " = '" . $db_field_name . "';";
if (stripos($content, $const_definition) === false) {
    $search_beginning = 'class ' . $entity_repository_class . ' extends EntityRepository';
    $position_of_class_beginning = stripos($content, $search_beginning) + strlen($search_beginning) + 2;

    // Insert field const
    $content = substr_replace($content, $const_definition, $position_of_class_beginning, 0);

    // Write file and read to memory again
    file_put_contents($entity_repository_file, $content);
    $content = file_get_contents($entity_repository_file);
}

// Check and add constant for db array
$field_definition = "\nself::" . $const_field_name . " => [\n'type' => '" . $field_type . "',\n],";
if (stripos($content, $field_definition) === false) {
    $search_beginning = "'fields' => [";
    $position_of_field_array = stripos($content, $search_beginning) + strlen($search_beginning);

    // Insert field const
    $content = substr_replace($content, $field_definition, $position_of_field_array, 0);

    // Write file
    file_put_contents($entity_repository_file, $content);
}

// Migration

// Load class
$entity_file = str_replace('Repository', '', $entity_repository_file);
$entity_class = str_replace('Repository', '', $entity_repository_class);

require_once $entity_file;

// Tokenizer to get class namespace
$fp = fopen($entity_file, 'rb');
$class = $namespace = $buffer = '';
$i = 0;
while (!$class) {
    if (feof($fp)) {
        break;
    }

    $buffer .= fread($fp, 512);
    $tokens = token_get_all($buffer);

    if (strpos($buffer, '{') === false) continue;

    for (; $i < count($tokens); $i++) {
        if ($tokens[$i][0] === T_NAMESPACE) {
            for ($j = $i + 1; $j < count($tokens); $j++) {
                if ($tokens[$j][0] === T_STRING) {
                    $namespace .= '\\' . $tokens[$j][1];
                } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                    break;
                }
            }
        }

        if ($tokens[$i][0] === T_CLASS) {
            for ($j = $i + 1; $j < count($tokens); $j++) {
                if ($tokens[$j] === '{') {
                    $class = $tokens[$i + 2][1];
                }
            }
        }
    }
}

/** @var Entity $entity */
$full_class_name = $namespace . '\\' . $entity_class;
$entity = new $full_class_name;

$migration_file = DIR_MIGRATIONS . date('Y_m_d_') . NOW . '.sql';
$migration_sql = MySQL::generateCreateColumnSQL($entity->getDbTableName(), $field_name, $field_type);
file_put_contents($migration_file, $migration_sql);

Updater::getInstance()->runMigrationFile($migration_file);

$message = 'Generated constant in Repository for "' . $entity_repository_class . '", added create field SQL in migrations for ' . $field_name . 'in file ' . $migration_file . ', migration applied in DB table ' . $entity->getDbTableName();
Messages::sendGreenAlert($message);
App::add($message);

back();
