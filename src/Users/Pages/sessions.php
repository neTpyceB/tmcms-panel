<?php
declare(strict_types=1);

// Kick other sessions
use TMCms\Admin\Entity\UsersSessionEntityRepository;
use TMCms\Admin\Users\Entity\AdminUserRepository;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTableHelper;

if (isset($_GET['_kick_my_sessions'])) {
    $sessions = new UsersSessionEntityRepository();
    $sessions->setWhereUserId(USER_ID);
    $sessions->addWhereFieldIsNot('sid', $_SESSION['admin_sid']);
    $sessions->deleteObjectCollection();

    back();
}

BreadCrumbs::getInstance()
    ->addCrumb(__('All Sessions'))
    ->addAction(__('Kick all my sessions except current'), SELF . '&_kick_my_sessions');

$sessions = new UsersSessionEntityRepository();

$users = new AdminUserRepository;
$users = $users->getPairs('login');

echo CmsTableHelper::outputTable([
    'data'              => $sessions,
    'callback_function' => function ($data) {
        $sid = $_SESSION['admin_sid'];
        foreach ($data as & $v) {
            if (USER_ID == $v['user_id']) {
                $v['sid'] = '<span style="color: ' . ($sid == $v['sid'] ? 'green' : 'red') . '">' . $v['sid'] . '</span>';
            }
        }

        return $data;
    },
    'columns'           => [
        'sid'     => [
            'title' => 'Session ID',
            'html'  => true,
        ],
        'ip_long' => [
            'title' => 'IP',
            'type'  => 'iplong',
        ],
        'user_id' => [
            'title' => 'User',
            'pairs' => $users,
        ],
        'ts'      => [
            'title' => 'Date',
            'type'  => 'datetime',
        ],
        'kick'    => [
            'title' => 'Kick',
            'href'  => '?p=' . P . '&do=_kick&sid={%id%}',
        ],
    ],
    'filters'           => [
        'user_id' => [
            'options'     => [-1 => 'All'] + $users,
            'auto_submit' => true,
        ],
    ],
]);