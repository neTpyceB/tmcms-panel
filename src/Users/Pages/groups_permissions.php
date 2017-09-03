<?php
declare(strict_types=1);

use TMCms\Admin\Menu;
use TMCms\Admin\Users;
use TMCms\Admin\Users\Entity\GroupAccessRepository;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\Modules\ModuleManager;
use TMCms\Strings\Converter;

$id = (int)$_GET['id'];
if (!$id) {
    return;
}

$res = [];

// Disable adding menu items while scanning files
Menu::getInstance()->setMayAddItemsFlag(false);

BreadCrumbs::getInstance()
    ->addCrumb(__('Edit Permissions'))
    ->addCrumb(Users::getInstance()->getGroupData('title', $id));

$all_menu_items = [
    'components'  => [],
    'filemanager' => [],
    'home'        => [],
    'structure'   => [],
    'users'       => [],
    'tools'       => [],
];

// Combine items
$custom_items = [];
if (file_exists(DIR_FRONT . 'menu.php')) {
    $custom_items = include DIR_FRONT . 'menu.php';
}
$all_menu_items = array_merge($custom_items, $all_menu_items);

// For every main menu item search for module and submenu
foreach ($all_menu_items as $main_menu_key => $main_menu_data) {
    $menu_class = 'TMCms\Admin\\' . Converter::toCamelCase($main_menu_key) . '\\Cms' . Converter::toCamelCase($main_menu_key);
    if (!class_exists($menu_class)) {
        $menu_class = 'TMCms\Modules\\' . Converter::toCamelCase($main_menu_key) . '\\Cms' . Converter::toCamelCase($main_menu_key);
    }
    ModuleManager::requireModule($main_menu_key);
    if (class_exists($menu_class)) {
        $reflection = new \ReflectionClass($menu_class);
        $filename = $reflection->getFileName();
        $folder = dirname($filename);
        $module_menu_file = $folder . '/menu.php';
        if (file_exists($module_menu_file)) {
            $module_menu_data = include $module_menu_file;
            $all_menu_items[$main_menu_key] = array_merge($all_menu_items[$main_menu_key], $module_menu_data);
        }
    }
}

foreach ($all_menu_items as $line => $line_data) {
    $line = trim($line);
    // Skip empty lines
    if (!$line) {
        continue;
    }

    $name = $line;

    // Key and value name of module
    if (strpos($line, ':') !== false) {
        $name = explode(':', $line);
        $name = $name[0];
    }

    $methods = [];

    // Maybe we need to init module
    ModuleManager::requireModule($name);

    // Try to load module from Vendor
    $class = '\TMCms\Modules\\' . Converter::toCamelCase($name) . '\Cms' . Converter::toCamelCase($name);

    // If autoloaded from modules
    if (class_exists($class)) {
        $methods = get_class_methods($class);
    } else {
        // Load main admin panel pages
        $class = '\TMCms\Admin\\' . Converter::toCamelCase($name) . '\Cms' . Converter::toCamelCase($name);
        if (class_exists($class)) {
            $methods = get_class_methods($class);
        }
    }

    // Remove methods that are not used for pages
    $tmp = [];
    foreach ($methods as $method) {
        // Skip singleton instances
        if ($method === 'getInstance') {
            continue;
        }
        // Skip internal callbacks
        if (stripos($method, 'callback') !== false) {
            continue;
        }
        // Skip internal form renders and magic methods
        if (strpos($method, '__') === 0) {
            continue;
        }

        $tmp[] = $method;
    }

    sort($tmp);
    $res[$line] = $tmp;
}

// Start to draw forms with current access
$full_access = Users::getInstance()->getGroupData('full_access', $id);
$access = [];

$access_repo = new GroupAccessRepository();
$access_repo->addSimpleSelectFields(['p', 'do']);
$access_repo->setWhereGroupId($id);

foreach ($access_repo->getAsArrayOfObjectData() as $v) {
    $access[$v['p']][$v['do']] = 1;
}

$form = CmsForm::getInstance()
//    ->setButtonSubmit(new CmsButton('Update'))
//    ->setAction('?p=' . P . '&do=_groups_permissions&id=' . $id)
;

$fields = [
    'all_permissions' => [
        'title'   => 'Full access',
        'type'    => 'checkbox',
        'onclick' => 'groups_permissions.checkAll(this);',
        'checked' => $full_access,
    ],
    'hr'              => [
        'title' => '',
        'type'  => 'row',
        'value' => '<br>',
    ],
];

// Draw access checkboxes
foreach ($res as $k => $v) {
    // Module name and representation
    if (strpos($k, ':') !== false) {
        list($name, $translation) = explode(':', $k);
    } else {
        $name = $translation = $k;
    }

    // Labels
    $inputs = [];
    foreach ($v as $val) {
        $inputs[] = '<label><input name="' . $name . '[' . $val . ']" type="checkbox" value="1" ' . ($full_access || isset($access[$name][$val]) ? 'checked="checked"' : '') . '> ' . $val . '</label>';
    }
    // Draw checkboxes
    $fields[$k] = [
        'title' => '<strong style="cursor: pointer" onclick="groups_permissions.checkRow(\'' . $name . '\');">' . __(ucfirst($translation)) . '</strong>',
        'type'  => 'html',
        'value' => implode('&nbsp;&nbsp;, &nbsp;&nbsp;&nbsp;&nbsp;', $inputs),
    ];
}

echo CmsFormHelper::outputForm([
    'submit' => __('Update'),
    'action' => '?p=' . P . '&do=_groups_permissions&id=' . $id,
    'fields' => $fields,
]);

// Script for inserting checkboxes
?>
    <!--suppress ALL -->
    <script>
        var groups_permissions = {
            // Full permisions
            checkAll: function (o) {
                if (o.checked) {
                    $('[type=checkbox]').prop('checked', true).parent('span').addClass('checked');
                } else {
                    $('[type=checkbox]').prop('checked', false).parent('span').removeClass('checked');
                }
            },
            // One module permissions
            checkRow: function (num) {
                if (!$('[name^=' + num + ']:checked').length) {
                    $('[name^=' + num + ']').prop('checked', true).parent('span').addClass('checked');
                } else {
                    $('[name^=' + num + ']').prop('checked', false).parent('span').removeClass('checked');
                }
                this.check_all_permissions_tick();
            },
            // If every is checked = check All Permissions
            check_all_permissions_tick: function () {
                if ($('input[type=checkbox]:not(:checked)').not('#all_permissions').length == 0) {
                    // All checked - set Full access
                    $('#all_permissions').prop('checked', true).parent('span').addClass('checked');
                } else {
                    $('#all_permissions').prop('checked', false).parent('span').removeClass('checked');
                }
            }
        };

        // Clicking on any checkbox - set all permission to off
        $('input[type=checkbox]').not('#all_permissions').change(function () {
            $('#all_permissions').prop('checked', false);
            groups_permissions.check_all_permissions_tick();
        });
    </script>
<?php