<?php
declare(strict_types=1);

use TMCms\Admin\Menu;
use TMCms\Admin\Users;
use TMCms\Admin\Users\Entity\AdminUserGroupRepository;
use TMCms\Admin\Users\Entity\AdminUserRepository;
use TMCms\Admin\Users\Entity\UserLogRepository;
use TMCms\Admin\Users\Entity\UsersMessageEntityRepository;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTableHelper;

Menu::getInstance()
    ->addHelpText('These are CMS system users only');

BreadCrumbs::getInstance()
    ->addCrumb(__('All Users'));

if (Users::getInstance()->checkAccess(P, 'users_add')) {
    BreadCrumbs::getInstance()
        ->addAction(__('Add User'), '?p=' . P . '&do=users_add');
}

$log = new UserLogRepository();
$messages = new UsersMessageEntityRepository();

$groups = new AdminUserGroupRepository();

$users = new AdminUserRepository();
$users->addOrderByField('login');
$users->addSimpleSelectFields(['id', 'login', 'active', 'group_id']);
$users->addSimpleSelectFieldsAsAlias('id', 'user_id');
$users->addSimpleSelectFieldsAsString('(SELECT COUNT(*) FROM `' . $log->getDbTableName() . '` AS `l` WHERE `l`.`user_id` = `' . $users->getDbTableName() . '`.`id`) AS `log`');
$users->addSimpleSelectFieldsAsString('(SELECT COUNT(*) FROM `' . $messages->getDbTableName() . '` AS `m` WHERE `m`.`to_user_id` = `' . $users->getDbTableName() . '`.`id`) AS `messages`');
// Skip admin user in list
if (USER_ID != 1) {
    $users->addWhereFieldIsNot('id', '1');
}

echo CmsTableHelper::outputTable([
    'data'              => $users,
    'save'              => true,
    'actions'           => [
        [
            'link' => '?p=' . P . '&do=_multiple_active',
            'name' => __('Activate'),
        ],
        [
            'link'    => '?p=' . P . '&do=_multiple_delete',
            'confirm' => true,
            'name'    => __('Delete'),
        ],
        [
            'link' => '?p=' . P . '&do=_multiple_export',
            'name' => __('Export'),
        ],
    ],
    'active'            => true,
    'edit'              => true,
    'delete'            => true,
    'columns'           => [
        'login'    => [
            'order' => true,
        ],
        'group_id' => [
            'order' => true,
            'title' => __('Group'),
            'href'  => '?p=' . P . '&do=show_all_users&group_id={%group_id%}',
            'pairs' => $groups->getPairs('title'),
        ],
        'online'   => [
            'type' => 'done',
        ],
        'messages' => [
            'href'   => '?p=' . P . '&do=chat&user_id={%id%}',
            'narrow' => true,
        ],
        'log'      => [
            'href'   => '?p=' . P . '&do=log&user_id={%id%}',
            'narrow' => true,
            'order'  => true,
        ],
    ],
    'callback_function' => function ($data) {
        foreach ($data as & $v) {
            $v['online'] = Users::getInstance()->isOnline($v['user_id']);
        }

        return $data;
    },
    'filters'           => [
        'group_id' => [
            'title'       => 'Group',
            'options'     => [-1 => 'All'] + Users::getInstance()->getGroupsPairs(),
            'auto_submit' => true,
        ],
        'active'   => [
            'options'     => [-1 => 'All', 0 => 'No', 1 => 'Yes'],
            'auto_submit' => true,
        ],
        'login'    => [
            'auto_submit' => true,
        ],
    ],
]);