<?php

use TMCms\Admin\Structure\Entity\PageComponentCustomEntityRepository;
use TMCms\Admin\Structure\Entity\PageComponentHistoryRepository;
use TMCms\Admin\Structure\Entity\PageComponentEntityRepository;
use TMCms\Admin\Structure\Entity\PageEntity;
use TMCms\Admin\Structure\Entity\PageTemplateEntity;
use TMCms\Files\Finder;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\Element\CmsButton;
use TMCms\HTML\Cms\Element\CmsCheckbox;
use TMCms\HTML\Cms\Element\CmsRow;
use TMCms\Routing\Entity\PageComponentsDisabledEntityRepository;
use TMCms\Routing\Structure;
use TMCms\Strings\Converter;
use TMCms\Templates\Components;
use TMCms\Templates\PageHead;
use TMCms\Templates\Plugin;
use TMCms\Templates\RenderComponentHelper;

defined('INC') or exit;

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    return;
}
$id = (int)$_GET['id'];

// Ensure exists
$components = new PageComponentEntityRepository();
$components = new PageComponentCustomEntityRepository();
$components = new PageComponentsDisabledEntityRepository();
$components = new PageComponentHistoryRepository();

$page = new PageEntity($id);
$template = new PageTemplateEntity($page->getTemplateId());

$lang = \TMCms\Routing\Languages::getShortByPageId($id);

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

foreach ($editable_elements as $v) {
    $fields = [];

    // Column for disabled
    $field = CmsCheckbox::getInstance('disabled_elements[' . $v['class'] . ']');
    if (in_array($v['class'], $disabled)) {
        $field->setChecked(true);
    }
    $fields[] = ['name' => 'Disable component', 'field' => $field];

    // Column for cached
    $field = CmsCheckbox::getInstance('cached_elements[' . $v['class'] . ']');
    if (in_array($v['class'], $cached)) {
        $field->setChecked(true);
    }
    $fields[] = ['name' => 'Hard cache component', 'field' => $field];

    // Show editable fields
    switch ($v['type']) {
        case 'component':

            /* COMPONENTS */
            if (!is_array($v['elements'])) {
                continue;
            }

            foreach ($v['elements'] as $element_key => $element_data) {
                // Provided simple strings
                if (!is_array($element_data)) {
                    $element_key = $element_data;
                    $element_data = ['title' => Converter::symb2Ttl($element_data)];
                }

                // Title for row
                if (!isset($element_data['title'])) {
                    $element_data['title'] = Converter::symb2Ttl($element_key);
                }

                // Defaults
                if (isset($element_data['options']) && !isset($element_data['type'])) {
                    $element_data['type'] = 'select';
                }

                $type = isset($element_data['type']) ? $element_data['type'] : 'text';
                $edit = isset($element_data['edit']) ? $element_data['edit'] : '';
                $component_name = $v['class'] . '_' . $element_key;

                if(!isset($element_data['lng']))
                    $element_data['lng'] = $lang;

                $field = $component_helper
                    ->setComponentName($component_name)
                    ->setFieldData($element_data)
                    ->setFieldType($type)
                    ->setWidgetType($edit)
                    ->getFieldView();

                if (!$field) {
                    continue;
                }

                // Current data of input
                if (isset($data[$component_name]) && $data[$component_name]) {
                    if (get_class($field) == 'TMCms\HTML\Cms\Element\CmsMultipleSelect') {
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
            }

            break;

        case 'plugin':
            $need_to_load_plugin_scripts = true; // To load JS for Plugins

            /* PLUGINS */
            foreach ($v['elements'] as $element_key => $element_data) {
                // Defaults
                $type = isset($element_data['type']) ? $element_data['type'] : 'text';
                $edit = isset($element_data['edit']) ? $element_data['edit'] : '';
                $component_name = $v['class'] . '_' . $element_key;

                // Title for row
                if (!isset($element_data['title'])) {
                    $element_data['title'] = Converter::symb2Ttl($element_key);
                }

                $field = $component_helper
                    ->setComponentName($component_name)
                    ->setFieldData($element_data)
                    ->setFieldType('select')
                    ->setWidgetType('')
                    ->setPageId($id)
                    ->getFieldView();

                // Bind JS function to load Plugin fields on select
                $field->setOnchange('cms_plugins.render_component(this);');

                $fields[] = ['name' => $element_data['title'], 'field' => $field];

                // Now we show fields for this plugin if it was already saved
                if ($component_helper->selectedOption()) {
                    $selected = $component_helper->selectedOption();
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
                            $comp_data = ['title' => Converter::symb2Ttl($comp_data)];
                        }

                        // Title for row
                        if (!isset($comp_data['title'])) {
                            $comp_data['title'] = Converter::symb2Ttl($comp_key);
                        }

                        // Defaults
                        $type = isset($comp_data['type']) ? $comp_data['type'] : 'text';
                        $edit = isset($comp_data['edit']) ? $comp_data['edit'] : '';

                        // Add prefix for plugin fields
                        $component_name = $v['class'] . '_' . $element_key . '_' . $comp_key;

                        $field = $component_helper
                            ->setComponentName($component_name)
                            ->setFieldData($comp_data)
                            ->setFieldType($type)
                            ->setWidgetType($edit)
                            ->getFieldView();

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

                        // Add plugin fields in own field set
                        $fields[] = ['name' => 'Plugin ' . $element_data['title'], 'field' => CmsRow::getInstance('')->setValue($plugin_form)];
                    }
                }
            }

            break;
    }

    // Add created field to form
    if ($fields) {
        $have_any_field = true;
        $form->addFieldBlock('<strong>' . Converter::symb2Ttl($v['class']) . '</strong>', $fields);
    }
}

$breadcrumbs = BreadCrumbs::getInstance()
    ->addCrumb('<a href="?p=' . P . '">' . ucfirst(P) . '</a>')
    ->addCrumb('Edit Page Components')
    ->addCrumb($page->getTitle());

// Links to other language versions
foreach (\TMCms\Routing\Languages::getPairs() as $short => $full) {
    $lng_page_id = Structure::getIdByLabel($page->getStringLabel(), $short);
    if ($lng_page_id) {
        $lqry = str_replace('&id=' . $page->getId(), '', QUERY) . '&id=' . $lng_page_id;
        $lurl = explode('?', SELF);
        $breadcrumbs->addPills(strtoupper($short) . ' version', $lurl[0] . '?' . $lqry, $page->getId() == $lng_page_id);
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
    ->setCancelButton(CmsButton::getInstance(__('Cancel')))
    ->setSubmitButton(CmsButton::getInstance(__('Update')));

if ($need_to_load_plugin_scripts) {
    // Include script for plugins
    PageHead::getInstance()->addJsUrl('cms_plugins.js');
}
