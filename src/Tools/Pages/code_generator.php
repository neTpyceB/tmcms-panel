<?php

use TMCms\HTML\Cms\CmsFormHelper;

defined('INC') or exit;

$existing_component_classes = array_map(function ($value) {
    return pathinfo($value, PATHINFO_FILENAME);
}, array_diff(scandir(DIR_FRONT_CONTROLLERS), ['.', '..']));

echo CmsFormHelper::outputForm('', [
    'action' => '?p=' . P . '&do=_generate_method',
    'title'  => 'Generate MVC method',
    'fields' => [
        'component_class'  => [
            'title'   => 'Controller/View class basename',
            'type'    => 'datalist',
            'options' => $existing_component_classes,
        ],
        'component_method' => [
            'title' => 'Method name',
        ],
    ],
    'button' => 'Generate',
]);