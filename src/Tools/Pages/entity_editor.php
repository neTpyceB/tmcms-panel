<?php
declare(strict_types=1);

use TMCms\Container\Post;
use TMCms\DB\SQL;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\Strings\Converter;

$selected_table_index = Post::getInstance()->getCleanedFieldAsInt('table_name') ?: 0;
$selected_entity_id = Post::getInstance()->getCleanedFieldAsInt('entity_id') ?: 0;

$all_entities = [];
// We use not correct approach. TODO get list of entites from class files.
// TODO make link in all edit forms from entities (Edit all fields or View all editable fields), or make a common helper
$tables = array_values(array_filter(SQL::getInstance()->getTables(), function ($table) {
    return strpos($table, 'm_') === 0;
}));

$all_entities = [0 => '---'] + $tables;

echo CmsFormHelper::outputForm([
    'action' => '?p='. P . '&do='. P_DO,
    'button' => 'Generate form for editing fields',
    'fields' => [
        'table_name' => [
            'selected' => $selected_table_index,
            'options' => $all_entities,
        ],
        'entity_id' => [
            'name' => 'ID',
            'value' => (string)$selected_entity_id,
        ],
    ],
]);

$selected_table_name = $all_entities[$selected_table_index];

if (!$selected_table_name || !$selected_entity_id || !$selected_table_index) {
    return;
}

$entity_data = q_assoc_row('SELECT * FROM `'. $selected_table_name .'` WHERE `id` = "'. $selected_entity_id .'"');

$columns = SQL::getTableColumns($selected_table_name);
$field_comments = [];
foreach (SQL::getColumnsComments($selected_table_name) as $comment_data) {
    $field_comments[$comment_data['COLUMN_NAME']] = $comment_data['COLUMN_COMMENT'];
}
$fields = [
    'table_name' => [
        'type' => 'hidden',
        'value' => $selected_table_name,
    ],
    'entity_id' => [
        'type' => 'hidden',
        'value' => (string)$selected_entity_id,
    ],
];

foreach ($columns as $column) {
    // Skip primary key
    if ($column['Field'] === 'id') {
        continue;
    }

    $field = [
        'name' => Converter::charsToNormalTitle($column['Field']),
    ];

    // Active
    if ($column['Field'] === 'active') {
        $field['type'] = 'checkbox';
    }

    // Order
    if ($column['Field'] === 'order') {
        $field['type'] = 'number';
        $field['min'] = 0;
        $field['max'] = 999999999;
        $field['step'] = 1;
        $field['required'] = true;
    }

    // Translation field
    if ($field_comments[$column['Field']] === 'translation') {
        $field['translation'] = true;
    }

    // TODO to show editors combine repository fields with db fields

    $fields[$column['Field']] = $field;
}

echo CmsFormHelper::outputForm([
    'data' => $entity_data,
    'action' => '?p='. P . '&do=_entity_editor',
    'button' => 'Save Entity data',
    'fields' => $fields,
]);
