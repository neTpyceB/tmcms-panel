<?php
declare(strict_types=1);

use TMCms\Admin\Structure\Entity\PageComponentCustomEntityRepository;
use TMCms\Admin\Structure\Entity\PageComponentEntityRepository;
use TMCms\Admin\Structure\Entity\PageComponentHistoryRepository;
use TMCms\Admin\Structure\Entity\PageEntity;
use TMCms\Admin\Structure\Entity\PageTemplateEntity;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\Element\CmsButton;
use TMCms\HTML\Cms\Element\CmsCheckbox;
use TMCms\Routing\Entity\PageComponentsDisabledEntityRepository;
use TMCms\Routing\Languages;
use TMCms\Routing\Structure;
use TMCms\Strings\Converter;
use TMCms\Templates\Components;
use TMCms\Templates\PageHead;
use TMCms\Templates\PageTail;
use TMCms\Templates\Plugin;
use TMCms\Templates\RenderComponentHelper;

defined('INC') or exit;

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    return;
}
$id = (int)$_GET['id'];

// Ensure exists
new PageComponentEntityRepository();
new PageComponentCustomEntityRepository();
new PageComponentsDisabledEntityRepository();
new PageComponentHistoryRepository();

$page = new PageEntity($id);
$template = new PageTemplateEntity($page->getTemplateId());

$lng = Languages::getShortByPageId($id);

// Usual components
$data = q_pairs('SELECT `component`, `data` FROM `cms_pages_components` WHERE `page_id` = "' . $id . '"');

$disabled = Structure::getDisabledComponents($id);
$cached = Structure::getCachedComponents($id);

$need_to_load_plugin_scripts = false;

$template_to_render = DIR_FRONT_TEMPLATES . $template->getFile();
$editable_elements = Components::outputForCms($template_to_render);

$have_any_field = false;

$form = CmsForm::getInstance();
$component_helper = new RenderComponentHelper();
$component_helper->setData($data);

// Clear field data to prepare for render
$clear_component_field_data = function($class, $element_key, $element_data, $lng) {
    // Provided simple strings
    if (!is_array($element_data)) {
        $element_key = $element_data;
        $element_data = ['title' => Converter::charsToNormalTitle($element_data)];
    }

    // Title for row
    if (!isset($element_data['title'])) {
        $element_data['title'] = Converter::charsToNormalTitle($element_key);
    }

    // For translation tabs
    if (!isset($element_data['lng'])) {
        $element_data['lng'] = $lng;
    }

    // Defaults
    if (isset($element_data['options']) && !isset($element_data['type'])) {
        $element_data['type'] = 'select';
    }

    $type = $element_data['type'] ?? 'text';
    $edit = $element_data['edit'] ?? '';
    $component_name = $class . '_' . $element_key;

    return compact('element_data', 'type', 'edit', 'component_name','element_key');
};

foreach ($editable_elements as $v) {
    $fields = [];

    // Column for disabled
    $field = CmsCheckbox::getInstance('disabled_elements[' . $v['class'] . ']');
    if (in_array($v['class'], $disabled, true)) {
        $field->setChecked(true);
    }
    $fields[] = ['name' => 'Disable component', 'field' => $field];

    // Column for cached
    $field = CmsCheckbox::getInstance('cached_elements[' . $v['class'] . ']');
    if (in_array($v['class'], $cached, true)) {
        $field->setChecked(true);
    }
    $fields[] = ['name' => 'Hard cache component', 'field' => $field];

    // Show editable fields
    if (!is_array($v['elements'])) {
        continue;
    }

    foreach ($v['elements'] as $element_key => $element_data) {
        $is_plugin = !empty($element_data['plugin']);

        $clear_field = $clear_component_field_data($v['class'], $element_key, $element_data, $lng);
        $component_name = $clear_field['component_name'];
        $type = $clear_field['type'];
        $edit = $clear_field['edit'];
        $element_data = $clear_field['element_data'];
        $element_key = $clear_field['element_key'];

        // Special case for plugins
        if ($is_plugin) {
            $need_to_load_plugin_scripts = true; // To load JS for Plugins
            $type = 'select';
            $element_data['options'] = ['---'] + ($element_data['options'] ?? Plugin::getInstance()->getPluginFilePairs());
        }

        $field = $component_helper
            ->setComponentName($component_name)
            ->setFieldData($element_data)
            ->setFieldType($type)
            ->setWidgetType($edit)
            ->getFieldView();

        // Error with fields
        if (!$field) {
            continue;
        }

        // Required for JS functions
        $field->setAttribute('data-page_id', $id);

        // Special case for plugins
        if ($is_plugin) {
            // Bind JS function to load Plugin fields on select
            $field->setOnchange('cms_plugins.render_component(this);');
            PageTail::getInstance()->addJs('cms_plugins.render_component(document.getElementById("'. $field->getId() .'"));');
        }

        // Current data of input
        if (isset($data[$component_name]) && $data[$component_name]) {
            if (get_class($field) === 'TMCms\HTML\Cms\Element\CmsMultipleSelect') {
                $field->setValue(unserialize($data[$component_name]));
            } else {
                $field->setValue($data[$component_name]);
            }
        } elseif (isset($element_data['value']) && $element_data['value']) {
            // Try to set default value from Controller
            if (is_array($element_data['value']) && isset($element_data['value'][LNG])) {
                $field->setValue($element_data['value'][LNG]); // One value for each language
            } else {
                $field->setValue($element_data['value']); // Common value
            }
        }

        // Add to form
        $fields[] = ['name' => $element_data['title'], 'field' => $field];

        // Plugin - rewrite or add some elements
//        if ($is_plugin) {
//            // Now we show fields for this plugin if it was already saved
//            if ($component_helper->selectedOption()) {
//                $selected = $component_helper->selectedOption();
//
//                // Require file with plugin class
//                $file_with_plugin = Finder::getInstance()->searchForRealPath($selected, Finder::TYPE_PLUGINS);
//                require_once DIR_BASE . $file_with_plugin;
//
//                // Create plugin object
//                $plugin_class_name = str_replace(['.php', 'plugin'], ['', 'Plugin'], $selected); // Just in case
//                /** @var Plugin $plugin_object */
//                $plugin_object = new $plugin_class_name;
//                $plugin_components = $plugin_object::getComponents();
//
//                $plugin_fields = [];
//
//                // Draw fields for plugin elements
//                foreach ($plugin_components as $comp_key => $comp_data) {
//
//                    $clear_field = $clear_component_field_data($v['class'], $element_key, $element_data, $lng);
//                    // Add prefix for plugin fields
//                    $component_name = $clear_field['component_name'] . '_' . $comp_key;
//                    $type = $clear_field['type'];
//                    $edit = $clear_field['edit'];
//                    $element_data = $clear_field['element_data'];
//                    $element_key = $clear_field['element_key'];
//
//                    $element_data['title'] = Converter::charsToNormalTitle($comp_key);
//
//                    $plugin_field = $component_helper
//                        ->setComponentName($component_name)
//                        ->setFieldData($comp_data)
//                        ->setFieldType($type)
//                        ->setWidgetType($edit)
//                        ->getFieldView();
//
//                    // Current data of input
//                    if (isset($data[$component_name])) {
//                        $plugin_field->setValue($data[$component_name]);
//                    }
//
//                    // Add to form
//                    $plugin_fields[] = ['name' => $element_data['title'], 'field' => $plugin_field];
//                }
//
//                if ($plugin_fields) {
//                    $plugin_form = CmsForm::getInstance();
//                    $plugin_form->disableFormTagOutput();
//                    $plugin_form->addFieldBlock('Fields', $plugin_fields);
//
//                    // Add plugin fields in own field set
//                    $fields[] = ['name' => 'Plugin ' . $element_data['title'], 'field' => CmsRow::getInstance('')->setValue($plugin_form)];
//                }
//            }
//        }
    }

    // Add created field to form
    if ($fields) {
        $have_any_field = true;
        $form->addFieldBlock('<strong>' . Converter::charsToNormalTitle($v['class']) . '</strong>', $fields);
    }
}

$breadcrumbs = BreadCrumbs::getInstance()
    ->addCrumb('<a href="?p=' . P . '">' . ucfirst(P) . '</a>')
    ->addCrumb('Edit Page Components')
    ->addCrumb($page->getTitle());

// Links to other language versions
foreach (Languages::getPairs() as $short => $full) {
    $lng_page_id = Structure::getIdByLabel($page->getStringLabel(), $short);
    if ($lng_page_id) {
        $language_query = str_replace('&id=' . $page->getId(), '', QUERY) . '&id=' . $lng_page_id;
        $language_urls = explode('?', SELF);
        $breadcrumbs->addPills(strtoupper($short) . ' version', $language_urls[0] . '?' . $language_query, $page->getId() == $lng_page_id);
    }
}
// To custom components
$breadcrumbs->addAction('Custom Components', '?p=structure&do=customs&id=' . $page->getId());
// To properties
$breadcrumbs->addAction('Page Properties', '?p=structure&do=edit_page&id=' . $page->getId());

if (!$have_any_field) {
    echo 'No any editable component';
    return;
}

echo $form
    ->enableAjax()
    ->setAction('?p=' . P . '&do=_edit_components&id=' . $id)
    ->setButtonCancel(CmsButton::getInstance(__('Cancel')))
    ->setButtonSubmit(CmsButton::getInstance(__('Update')));

if ($need_to_load_plugin_scripts) {
    // Include script for plugins
    PageHead::getInstance()->addJsUrl('cms_plugins.js');
}
