<?php
declare(strict_types=1);

use TMCms\Admin\Users;
use TMCms\Admin\Users\Entity\AdminUserGroupRepository;
use TMCms\Admin\Users\Entity\AdminUserRepository;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTableHelper;

BreadCrumbs::getInstance()
    ->addCrumb(__('All Groups'))
    ->addAction(__('Add Group'), '?p=' . P . '&do=groups_add');

$users = new AdminUserRepository();

$groups = new AdminUserGroupRepository();
$groups->addSimpleSelectFields(['id', 'title', 'default']);
$groups->addOrderByField('title');
// Skip admin group
if (Users::getInstance()->getGroupData('id') !== 1) {
    $groups->addWhereFieldIsNot('id', 1);
}
$groups->addSimpleSelectFieldsAsString('(SELECT COUNT(*) FROM `' . $users->getDbTableName() . '` AS `u` WHERE `u`.`group_id` = `' . $groups->getDbTableName() . '`.`id` AND IF("' . ((int)USER_ID == 1) . '", 1, `u`.`id` != "1")) AS `users`');

echo CmsTableHelper::outputTable([
    'data'    => $groups,
    'columns' => [
        'title'       => [
            'order' => true,
        ],
        'users'       => [
            'href'  => '?p=' . P . '&group_id={%id%}',
            'order' => true,
        ],
        'permissions' => [
            'value' => 'set',
            'href'  => '?p=' . P . '&do=groups_permissions&id={%id%}',
        ],
        'default'     => [
            'type' => 'active',
            'href' => '?p=' . P . '&do=_groups_default&id={%id%}',
        ],
    ],
    'edit'    => true,
    'delete'  => true,
]);