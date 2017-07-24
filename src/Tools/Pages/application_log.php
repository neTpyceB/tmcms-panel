<?php
declare(strict_types=1);

use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTableHelper;

defined('INC') or exit;

BreadCrumbs::getInstance()
    ->addCrumb('Application log');

$data_sql = '
SELECT
	`l`.`id`,
	`l`.`ts`,
	`l`.`msg`,
	`l`.`url`,
	`l`.`p`,
	`l`.`do`,
	`l`.`user_id`,
	CONCAT_WS(", ", `u`.`name`,`u`.`surname`, `u`.`login`) AS `user`
FROM `cms_app_log` AS `l`
LEFT JOIN `cms_users` AS `u` ON `u`.`id` = `l`.`user_id`
WHERE IF("' . ((int)USER_ID === 1) . '", 1, `u`.`id` != "1")
ORDER BY `l`.`ts` DESC
';

echo CmsTableHelper::outputTable([
    'data'              => $data_sql,
    'cut_long_strings'  => false,
    'callback_function' => 'TMCms\Admin\Tools\CmsTools::app_log_callback',
    'columns'           => [
        'ts'   => [
            'title' => 'Date',
            'type'  => 'datetime',
        ],
        'user' => [
            'html' => true,
        ],
        'p'    => [
            'title' => 'Page',
        ],
        'do'   => [
            'title' => 'Action',
        ],
        'msg'  => [
            'html' => true,
        ],
        'url'  => [],
    ],
]);