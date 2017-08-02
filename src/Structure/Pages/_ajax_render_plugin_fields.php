<?php

use TMCms\Files\Finder;
use TMCms\HTML\Cms\CmsForm;
use TMCms\Strings\Converter;
use TMCms\Templates\Plugin;
use TMCms\Templates\RenderComponentHelper;

defined('INC') or exit;

if (!isset($_GET['file'], $_GET['name'], $_GET['id'])) {
    return;
}
$id = (int)$_GET['id'];

ob_clean();

$data = q_pairs('SELECT `component`, `data` FROM `cms_pages_components` WHERE `page_id` = "'. $id .'"');

$selected = $_GET['file'];
if ($selected) {

    $parent_name = $_GET['name'];

    $component_helper = new RenderComponentHelper();
    $component_name = $parent_name;

    // Require file with plugin class
    $file_with_plugin = Finder::getInstance()->searchForRealPath($selected, Finder::TYPE_PLUGINS);
    require_once DIR_BASE . $file_with_plugin;

    // Create plugin object
    $plugin_class_name = str_replace('.php', '', $selected);
    $plugin_class_name = str_replace('plugin', 'Plugin', $plugin_class_name); // Just in case
    /** @var Plugin $plugin_object */
    $plugin_object = new $plugin_class_name;
    $plugin_components = $plugin_object->getComponents();

    $plugin_fields = [];

    // Draw fields for plugin elements
    foreach ($plugin_components as $comp_key => $comp_data) {

        // Provided simple strings
        if (!is_array($comp_data)) {
            $comp_key = $comp_data;
            $comp_data = ['title' => Converter::charsToNormalTitle($comp_data)];
        }

        // Title for row
        if (!isset($comp_data['title'])) {
            $comp_data['title'] = Converter::charsToNormalTitle($comp_key);
        }

        // Defaults
        $type = isset($comp_data['type']) ? $comp_data['type'] : 'text';
        $edit = isset($comp_data['edit']) ? $comp_data['edit'] : '';

        // Add prefix for plugin fields
        $component_name = $parent_name .'_'. $comp_key;

        $field = $component_helper
            ->setComponentName($component_name)
            ->setFieldData($comp_data)
            ->setFieldType($type)
            ->setWidgetType($edit)
            ->getFieldView()
        ;

        // Current data of input
        if (isset($data[$component_name])) {
            $field->setValue($data[$component_name]);
        }

        // Add to form
        $plugin_fields[] = ['name' => $comp_data['title'], 'field' => $field];
    }

    if ($plugin_fields) {
        $plugin_form = CmsForm::getInstance();
        $plugin_form->outputTagForm(false);
        $plugin_form->addFieldBlock('Fields', $plugin_fields);
        echo $plugin_form;
    }

}
die;