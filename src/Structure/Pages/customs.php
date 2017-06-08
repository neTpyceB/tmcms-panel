<?php

defined('INC') or exit;

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) return;
$id = (int)$_GET['id'];

$q = q_assoc_row('
SELECT
	`p`.`id`,
	`p`.`title`,
	`p`.`string_label`,
	`t`.`file`
FROM `cms_pages_templates` AS `t`
JOIN `cms_pages` AS `p` ON `p`.`template_id` = `t`.`id`
WHERE `p`.`id` = "'. $id .'"
');
if (!$q) return;

use TMCms\Admin\Structure\Entity\PageComponentCustomEntityRepository;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\CmsTabs;
use TMCms\HTML\Cms\Element\CmsButton;
use TMCms\HTML\Cms\Element\CmsRow;
use TMCms\Routing\Structure;
use TMCms\Strings\Converter;
use TMCms\Templates\Components;
use TMCms\Templates\RenderComponentHelper;

$component_helper = new RenderComponentHelper();
$template_to_render = DIR_FRONT_TEMPLATES . $q['file'];
$tabs = [];
$tab_origin_fields = [];
$origin_field_data = [];
$data = [];
$count_of_existing_data = 0;

$customs = new PageComponentCustomEntityRepository();
$customs->addSimpleSelectFields(['component', 'tab', 'name', 'value', 'order']);
$customs->setWherePageId($id);
$customs->addOrderByField();

$lang = \TMCms\Routing\Languages::getShortByPageId($id);

foreach ($customs->getAsArrayOfObjectData() as $v) {
    $data[$v['component']][$v['tab']][$v['order']][$v['name']] = $v;
}

foreach (Components::outputForCms($template_to_render) as $component_name => $component) {
    // Skip with no elements in components
    if (!isset($component['elements'])) {
        continue;
    }

    // Iterate all components
    foreach ($component['elements'] as $element_name => $element) {
        // If component is not custom
        if (!isset($element['type'], $element['fields']) || $element['type'] != 'custom') {
            continue;
        }

        $form = CmsForm::getInstance();
        $form->outputTagForm(false);
        $form->disableFullView();

        // Iterate fields
        foreach ($element['fields'] as $field_key => $field_data) {
            // Title for row
            if (!is_array($field_data)) {
                dump('Field "' . $field_data . '" must be array');
            }
            if (!isset($field_data['title'])) {
                $field_data['title'] = Converter::symb2Ttl($field_key);
            }

            // Defaults
            $field_type = isset($field_data['type']) ? $field_data['type'] : 'text';
            $field_edit = isset($field_data['edit']) ? $field_data['edit'] : '';

            $key_name = $component_name .'['. $element_name . '][' . $field_key .']';

            if (isset($data[$component_name][$element_name])) {
                $count_of_existing_data = count($data[$component_name][$element_name]);
            } else {
                $count_of_existing_data = 0;
            }

            if(!isset($field_data['lng']))
                $field_data['lng'] = $lang;

            $field = $component_helper
                ->setComponentName($key_name . '['. $count_of_existing_data .']')
                ->setFieldType($field_type)
                ->setWidgetType($field_edit)
                ->setFieldData($field_data)
                ->getFieldView()
            ;

            $origin_field_data[$key_name] = [
                'type' => $field_type,
                'widget' => $field_edit,
                'title' => $field_data['title'],
                'data' => $field_data,
            ];

            // Add to form
            $tab_origin_fields[$element_name][$field_key] = array('name' => $field_data['title'], 'field' => $field);

            if (isset($data[$component_name][$element_name])) {
                foreach ($data[$component_name][$element_name] as $key => $data_element) {
                    if (!isset($data[$component_name][$element_name][$key][$field_key])) {
                        $data[$component_name][$element_name][$key][$field_key] = [
                            'component' => $component_name,
                            'tab' => $element_name,
                            'name' => $field_key,
                            'value' => null,
                            'order' => $key
                        ];
                    }
                }

            }
        }

        // Check default values from Controller
        if (isset($element['value']) && is_array($element['value'])) {
            foreach ($element['value'] as $value_item_order => $value_item) {
                foreach ($value_item as $value_item_field_key => $value_item_field_name) {
                    if (!isset($data[$component_name][$element_name][$value_item_order][$value_item_field_key])) {
                        // Generate block for default values
                        $data[$component_name][$element_name][$value_item_order][$value_item_field_key] = [
                            'component' => $component_name,
                            'tab' => $element_name,
                            'name' => $value_item_field_key,
                            'value' => $value_item_field_name,
                            'order' => $value_item_order,
                        ];
                    }
                }
            }
        }

        $form->addFieldBlock('Add new block', $tab_origin_fields[$element_name]);

        // Now we create fields with existing data for every tab
        $i = 0;
        if (isset($data[$component_name][$element_name])) {
            foreach ($data[$component_name][$element_name] as $data_field_order => $data_field_data) {
                $all_fields = [];
                $block_name = '';
                foreach ($data_field_data as $field_name => $field_data) {
                    $key_name = $component_name .'['. $element_name . '][' . $field_name .']';

                    if (!isset($origin_field_data[$key_name])) {
                        continue;
                    }

                    $field_origin = $origin_field_data[$key_name];

                    if (!isset($field_origin['type'])) {
                        $field_origin['type'] = 'text';
                    }
                    if (!isset($field_origin['widget'])) {
                        $field_origin['widget'] = '';
                    }
                    if (isset($field_data['value'])) {
                        $field_origin['data']['selected'] = $field_data['value'];
                    }
                    if (!isset($field_origin['data'])) {
                        $field_origin['data'] = [];
                    }

                    $field = $component_helper
                        ->setComponentName($key_name . '[' . $data_field_order . ']')
                        ->setFieldType($field_origin['type'])
                        ->setWidgetType($field_origin['widget'])
                        ->setFieldData($field_origin['data'])
                        ->getFieldView()
                    ;

                    // Current data of input
                    $field->setValue($field_data['value']);

                    if ($field_origin['type'] == 'checkbox') {
                        if ($field_data['value'] == '1') {
                            $field->setChecked(1);
                            $field->setValue(1);
                        }
                    }

                    $all_fields[$field_name] = array('name' => $origin_field_data[$key_name]['title'], 'field' => $field);
                }

                if ($all_fields) {

                    // Link to order blocks
                    $order_links = '<a href="?p=' . P . '&do=_customs_delete&page_id=' . $q['id'] . '&component=' . $component_name . '&tab=' . $element_name . '&order=' . $i . '" class="nounderline" onclick="return confirm(\'Are you sure?\')"><i class="fa fa-trash-o"></i></a>&nbsp;&nbsp;&nbsp;';

                    if ($i) { // Not first
                        $order_links .= '<a href="?p=' . P . '&do=_customs_order&page_id=' . $q['id'] . '&component=' . $component_name . '&tab=' . $element_name . '&order=' . $i . '&direct=up" class="nounderline"><i class="fa fa-long-arrow-up"></i></a>&nbsp;&nbsp;&nbsp;';
                    } else {
                        $order_links .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                    if ($i != ($count_of_existing_data - 1)) { // Not last
                        $order_links .= '<a href="?p=' . P . '&do=_customs_order&page_id=' . $q['id'] . '&component=' . $component_name . '&tab=' . $element_name . '&order=' . $i . '&direct=down" class="nounderline"><i class="fa fa-long-arrow-down"></i></a>&nbsp;&nbsp;&nbsp;';
                    } else {
                        $order_links .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                    }

                    // Now we sort field to make it in way described in Controller order
                    $sorted = [];
                    foreach ($all_fields as $k => $v) {
                        $keys = array_keys($tab_origin_fields[$element_name]);
                        $key = array_search($k, $keys, true);
                        $v['original_key'] = $k;

                        if ($key !== false && array_key_exists($key, $keys)) {
                            // Found in db
                            $sorted[$key] = $v;
                        } else {
                            // Only in component structure
                            $sorted[] = $v;
                        }
                    }

                    ksort($sorted);

                    // Set first field value as name of block
                    foreach($sorted as $v){
                        if($data_field_data[$v['original_key']]['value']) {
                            $block_name = $data_field_data[$v['original_key']]['value'];
                            break;
                        }
                    }

                    $all_fields = $sorted;

                    // Create block with fields and data
                    $form->addFieldBlock('&nbsp;&nbsp;' . ($i + 1) . '&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;' . $order_links . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $block_name, $all_fields);
                    $i++;
                }
            }
        }

        $tabs[$element_name] = $form;

        $i++;
    }
}

$breadcrumbs = BreadCrumbs::getInstance()
    ->addCrumb('<a href="?p='. P .'">' . ucfirst(P) . '</a>')
    ->addCrumb('Edit Custom Components')
    ->addCrumb($q['title'])
;

// Links to other language versions
foreach (\TMCms\Routing\Languages::getPairs() as $short => $full) {
    $lng_page_id = Structure::getIdByLabel($q['string_label'], $short);
    if ($lng_page_id) {
        $lqry = str_replace('&id=' . $q['id'], '', QUERY) . '&id=' . $lng_page_id;
        $lurl = explode('?', SELF);
        $breadcrumbs->addPills(strtoupper($short) . ' version', $lurl[0] . '?' . $lqry, $q['id'] == $lng_page_id);
    }
}
// To components
$breadcrumbs->addAction('Components', '?p=structure&do=edit_components&id=' . $q['id']);
// To properties
$breadcrumbs->addAction('Page Properties', '?p=structure&do=edit_page&id=' . $q['id']);

$tabs_to_render = CmsTabs::getInstance();

foreach ($tabs as $tab_key => $tab_form) {
    $tabs_to_render->addTab($tab_key, $tab_form);
}

if (!$tabs_to_render->getTabs()) {
    echo 'No custom components defined';
    return;
}

echo CmsForm::getInstance()
    ->showSubmitOnTop(true)
    ->setAction('?p='. P .'&do=_customs&id='. $id)
    ->setSubmitButton(CmsButton::getInstance('Update All'))
    ->addField('', CmsRow::getInstance('form')->setValue($tabs_to_render))
;
?>
<script>
    // Set all checkboxex checked property
    $('input[type=checkbox]').each(function (k, v) {
        var $el = $(v);
        if ($el.attr('checked') == 'checked') {
            $el.attr('checked', 'checked');
            $el.prop('checked', true);
            $el.attr('value', '1');
        } else {
            $el.removeAttr('checked');
            $el.prop('checked', false);
        }
    });
    // Set all checkboxex checked property
    $('input[type=checkbox]').on('click', function () {
        var $el = $(this);
        if ($el.attr('checked') == 'checked') {
            $el.attr('checked', 'checked');
            $el.prop('checked', true);
            $el.attr('value', '1');
        } else {
            $el.removeAttr('checked');
            $el.prop('checked', false);
        }
    });
</script>