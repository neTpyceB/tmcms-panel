<?php

defined('INC') or exit;

use TMCms\Admin\Users\Entity\AdminUserRepository;
use TMCms\Admin\Users\Entity\UserLogRepository;
use TMCms\Config\Constants;

if (IS_AJAX_REQUEST) {
    ob_start();
}

$access_log = new UserLogRepository();
$access_log->addSimpleSelectFields(['id', 'ts', 'request_uri']);
$access_log->addOrderByField('ts', true);
$access_log->setLimit(20);

$users = new AdminUserRepository();
$users->addSimpleSelectFieldsAsString('CONCAT(`'. $users->getDbTableName() .'`.`name`, " ", `'. $users->getDbTableName() .'`.`surname`) AS `user`');
$users->addWhereFieldAsString('IF("' . ((int)USER_ID == 1) . '", 1, `'. $users->getDbTableName() .'`.`id` != "1")');

$access_log->mergeWithCollection($users, 'user_id');

$res = [];
foreach ($access_log->getAsArrayOfObjectData() as $v) {
    $res[] = date(Constants::FORMAT_CMS_DATETIME_FORMAT, $v['ts']) . ': ' . $v['user'] . ' - ' . $v['request_uri'];
}

echo '<b>User log</b><br>';
echo implode('<br>', $res);

if (IS_AJAX_REQUEST) {
    echo ob_get_clean(); die;
}
