<?php
declare(strict_types=1);

use TMCms\Files\FileSystem;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\Modules\ModuleManager;
use TMCms\Orm\TableStructure;

defined('INC') or exit;

// Make sure folders exist
FileSystem::mkDir(DIR_FRONT_CONTROLLERS);
FileSystem::mkDir(DIR_FRONT_VIEWS);

// MVC parts
$existing_component_classes = array_map(function ($value) {
    return pathinfo($value, PATHINFO_FILENAME);
}, array_diff(scandir(DIR_FRONT_CONTROLLERS, SCANDIR_SORT_NONE), ['.', '..']));

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
    'collapsed' => true,
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
    'collapsed' => true,
]);

// Module entities
echo CmsFormHelper::outputForm('', [
    'action' => '?p=' . P . '&do=_generate_module_entity',
    'title'  => 'Generate empty Entity in Module',
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
    'collapsed' => true,
]);

// Module entities
echo CmsFormHelper::outputForm([
    'id' => 'entity_field_form',
    'action' => '?p=' . P . '&do=_generate_entity_field',
    'title'  => 'Generate new field in Entity',
    'fields' => [
        'module_name'  => [
            'title'   => 'Module name',
            'options' => [-1 => '---'] + array_combine(ModuleManager::getListOfCustomModuleNames(), ModuleManager::getListOfCustomModuleNames()),
            'onchange' => 'load_module_entities(this)',
        ],
        'entity_name'  => [
            'title'   => 'Entity name',
            'options' => [0 => '---'],
        ],
        'field_type'  => [
            'title'   => 'Field type',
            'options' => [0 => '---'] + TableStructure::FIELD_TYPES_AVAILABLE,
        ],
        'field_name'  => [
            'title'   => 'Field name',
        ],
    ],
    'button' => 'Generate',
    'collapsed' => true,
]);

?>
<script>
    function load_module_entities(el) {
        var $el = $(el);

        var selected_module_name = $el.val();

        $.ajax({
            url: '?p=<?= P ?>&do=_ajax_get_module_entities',
            data: {
                ajax: 1,
                selected_module_name: selected_module_name
            },
            dataType: "json",
            success: function (data) {
                var $select =  $("#entity_field_form").find("#entity_name");
                // Empty and disable
                $select.find('option').remove();

                // Add new options
                $.each(data, function(k, v) {
                    $select.append('<option value="'+ k +'">'+ v +'</option>');
                });
            }
        });
    }
</script>
