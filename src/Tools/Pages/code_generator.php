<?php

use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\Modules\ModuleManager;

defined('INC') or exit;

// MVC parts
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

// Empty mModule
echo CmsFormHelper::outputForm('', [
    'action' => '?p=' . P . '&do=_generate_module',
    'title'  => 'Generate empty Module',
    'fields' => [
        'module_name'  => [
            'title'   => 'Module name',
        ],
    ],
    'button' => 'Generate',
]);

// Module entities
echo CmsFormHelper::outputForm('', [
    'action' => '?p=' . P . '&do=_generate_module_entity',
    'title'  => 'Generate Module empty Entity',
    'fields' => [
        'module_name'  => [
            'title'   => 'Module name',
            'options' => array_combine(ModuleManager::getListOfCustomModuleNames(), ModuleManager::getListOfCustomModuleNames()),
        ],
        'entity_name'  => [
            'title'   => 'Entity name',
            'hint' => 'For Entity and EntityRepository both',
        ],
    ],
    'button' => 'Generate',
]);
