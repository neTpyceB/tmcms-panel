<?php
declare(strict_types=1);

use TMCms\Admin\Menu;
use TMCms\Admin\Structure\Entity\StructurePagePermissionEntityRepository;
use TMCms\Admin\Users;
use TMCms\Admin\Users\Entity\AdminUserGroup;
use TMCms\DB\TableTree;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\HTML\Cms\Element\CmsCheckbox;
use TMCms\HTML\Cms\Element\CmsHtml;
use TMCms\HTML\Cms\Element\CmsInputHidden;

defined('INC') or exit;

// Ensure DB exists
new StructurePagePermissionEntityRepository();

Menu::getInstance()
    ->addHelpText('Permissions are set to all users in selected group')
    ->addHelpText('You can disable any common action with any page');

$group_id = isset($_POST['id']) ? (int)$_POST['id'] : false;

$group = new AdminUserGroup($group_id);

if (!$group_id) {
    BreadCrumbs::getInstance()
        ->addCrumb($group->getTitle())
        ->addAlerts('This will affect all users in selected group');

    echo CmsFormHelper::outputForm([
        'action' => SELF,
        'title'  => __('Select group'),
        'fields' => [
            'id' => [
                'title'    => 'User group',
                'options'  => ['---'] + Users::getInstance()->getGroupsPairs(),
                'onchange' => 'this.form.submit()',
            ],
        ],
    ]);

    return;
}

$permissions = new StructurePagePermissionEntityRepository();
$permissions->addSimpleSelectFieldsAsAlias('page_id', 'id');
$permissions->addSimpleSelectFields(['edit', 'properties', 'active', 'delete']);
$tmp = [];
foreach ($permissions->getAsArrayOfObjectData() as $perm) {
    $tmp[$perm['id']] = $perm;
}
$permissions = $tmp;

$full_access = Users::getInstance()->getGroupData('structure_permissions', $group_id);

BreadCrumbs::getInstance()
    ->addCrumb('Edit structure permissions for ' . $group->getTitle())
    ->addCrumb(Users::getInstance()->getGroupData('title', $group_id));

$form = CmsForm::getInstance()
    ->setFormTitle('Edit permissions')
    ->setAction('?p=' . P . '&do=_permissions')
    ->setButtonSubmit('Save')
    ->addField('', CmsInputHidden::getInstance('group_id')
        ->setValue($group_id)
    )
    ->addField('Group', CmsHtml::getInstance('group')
        ->setValue(Users::getInstance()->getGroupData('title', $group_id))
    )
    ->addField('Full Access', CmsCheckbox::getInstance('full_access')
        ->setChecked((bool)$full_access)
        ->setOnclick('structure_permissions.checkAll(this);')
    )
    ->addField('', CmsHtml::getInstance('')
        ->setValue('<br>')
    )
    ->addField('All Pages', CmsHtml::getInstance('all')
        ->setValue('<a class="jsButton" onclick="structure_permissions.checkColumn(\'edit\');return false">Edit content</a>, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class="jsButton" onclick="structure_permissions.checkColumn(\'properties\');return false">Edit properties</a>, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class="jsButton" onclick="structure_permissions.checkColumn(\'active\');return false">Activate/Deactivate</a>, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class="jsButton" onclick="structure_permissions.checkColumn(\'delete\');return false">Delete</a>')
    )
    ->addField('', CmsHtml::getInstance('')
        ->setValue('<br>')
    );

foreach (TableTree::getInstance('cms_pages')->setTitleColumn('title')->setOrderColumn('order')->getAsArray() as $page_k => $page_v) {
    if ($full_access) {
        $permissions[$page_k]['edit'] = $permissions[$page_k]['properties'] = $permissions[$page_k]['active'] = $permissions[$page_k]['delete'] = true;
    }
    $form->addField('<a class="jsButton" onclick="structure_permissions.checkRow(' . $page_k . ');return false">' . $page_v . '</a>', CmsHtml::getInstance((string)$page_k)->setValue(permissions_get_inputs($page_k, $permissions[$page_k] ?? [])));
}

echo $form;

/**
 * @param $page_id
 * @param $permissions
 *
 * @return string
 */
function permissions_get_inputs($page_id, $permissions)
{
    $inputs = [];
    foreach (['edit' => 'Edit content', 'properties' => 'Edit properties', 'active' => 'Activate/Deactivate', 'delete' => 'Delete'] as $k => $v) {
        $inputs[] = '<label><input name="' . $page_id . '[' . $k . ']" type="checkbox" value="1" ' . ((isset($permissions[$k]) && $permissions[$k]) ? 'checked="checked"' : NULL) . '> ' . $v . '</label>';
    }

    return implode('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $inputs);
}

?>
<script>
    var structure_permissions = {
        checkAll: function (o) {
            if (o.checked) {
                $('[type=checkbox]').prop('checked', true).parent('span').addClass('checked');
            } else {
                $('[type=checkbox]').prop('checked', false).parent('span').removeClass('checked');
            }
        },
        checkRow: function (num) {
            if (!$('[name^=' + num + ']:checked').length) {
                $('[name^=' + num + ']').prop('checked', true).parent('span').addClass('checked');
            } else {
                $('[name^=' + num + ']').prop('checked', false).parent('span').removeClass('checked');
            }
            this.check_all_permissions_tick();
        },
        checkColumn: function (type) {
            if (!$('[name*=' + type + ']:checked').length) {
                $('[name*=' + type + ']').prop('checked', true).parent('span').addClass('checked');
            } else {
                $('[name*=' + type + ']').prop('checked', false).parent('span').removeClass('checked');
            }
            this.check_all_permissions_tick();
        },
        check_all_permissions_tick: function () {
            if ($('input[type=checkbox]:not(:checked)').not('#full_access').length == 0) {
                // All checked - set Full access
                $('#full_access').prop('checked', true).parent('span').addClass('checked');
            } else {
                $('#full_access').prop('checked', false).parent('span').removeClass('checked');
            }
        }
    };

    $('input[type=checkbox]').not('#full_access').change(function () {
        structure_permissions.check_all_permissions_tick();
    });
</script>
