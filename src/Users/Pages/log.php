<?php
declare(strict_types=1);

use TMCms\Admin\Users;
use TMCms\Config\Settings;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTableHelper;

if (!Settings::isCmsUserLogEnabled()) {
    error('Log is not enabled');
}

$users = Users::getInstance()->getUsersPairs();
if (USER_ID !== 1) {
    unset($users[1]);
}

BreadCrumbs::getInstance()
    ->addCrumb('Admin User logs');

echo CmsTableHelper::outputTable([
    'data'              => '
SELECT
	`l`.`id`,
	`l`.`ts`,
	`l`.`user_id`,
	INET_NTOA(`l`.`ip_long`) AS `ip`,
	`l`.`agent`,
	`l`.`request_uri`,
	`l`.`referer`,
	`l`.`p`,
	`l`.`do`,
	`l`.`post`,
	CONCAT_WS(`u`.`name`, " ", `u`.`surname`) AS `user`
FROM `cms_users_log` AS `l`
JOIN `cms_users` AS `u` ON `u`.`id` = `l`.`user_id`
WHERE IF("' . ((int)USER_ID === 1) . '", 1, `u`.`id` != "1")
ORDER BY `l`.`ts` DESC
		',
    'callback_function' => function ($data) {
        foreach ($data as &$v) {
            // Self is green
            if ($v['user_id'] == USER_ID) {
                $v['user'] = '<span style="color: green">' . $v['user'] . '</span>';
            }
            // Show $_POST data
            if ($v['post']) {
                $v['post'] = '<var><var class="jsButton" style="cursor: pointer" onclick="this.style.display = \'none\'; document.getElementById(\'post_show_' . $v['id'] . '\').style.display = \'block\'">Show</var><div id="post_show_' . $v['id'] . '" style="display: none">' . htmlspecialchars($v['post'], ENT_QUOTES) . '</div></var>';
            }
        }

        return $data;
    },
    'columns'           => [
        'user'        => [
            'html'  => true,
            'order' => true,
        ],
        'ts'          => [
            'title' => 'Date',
            'type'  => 'datetime',
            'order' => true,
        ],
        'request_uri' => [
            'title'           => 'Page',
            'href'            => '{%request_uri%}',
            'href_new_window' => true,
            'order'           => true,
        ],
        'referer'     => [
            'href'            => '{%referer%}',
            'href_new_window' => true,
            'order'           => true,
        ],
        'p'           => [
            'title' => 'Module',
        ],
        'do'          => [
            'title' => 'Action',
        ],
        'ip'          => [
            'title' => 'IP',
        ],
        'agent'       => [
            'title' => 'Browser',
        ],
        'post'        => [
            'title' => 'POST',
            'html'  => true,
        ],
    ],
    'filters'           => [
        'user_id'     => [
            'options' => [-1 => 'All'] + $users,
            'title'   => 'Users',
        ],
        'request_uri' => [
            'title' => 'Page',
        ],
        'referer'     => [],
        'agent'       => [],
    ],
]);