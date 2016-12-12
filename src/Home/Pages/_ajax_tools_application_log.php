<?php

defined('INC') or exit;

if (IS_AJAX_REQUEST) {
    ob_start();
}

$application_log = [];

foreach (q_assoc_iterator('
SELECT
	`l`.`ts`,
	`l`.`msg`,
	CONCAT(`u`.`name`, " ", `u`.`surname`) AS `user`
FROM `cms_app_log` AS `l`
LEFT JOIN `cms_users` AS `u` ON `u`.`id` = `l`.`user_id`
WHERE IF("' . ((int)USER_ID == 1) . '", 1, `u`.`id` != "1")
ORDER BY `l`.`ts` DESC
LIMIT 20
') as $v) {
    $application_log[] = date(CFG_CMS_DATETIME_FORMAT, $v['ts']) . ': ' . $v['user'] . ' - ' . $v['msg'];
}

echo '<b>Application log</b><br>';
echo implode('<br>', $application_log);

if (IS_AJAX_REQUEST) {
    echo ob_get_clean(); die;
}