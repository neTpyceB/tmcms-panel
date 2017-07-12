<?php
declare(strict_types=1);

use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTableHelper;
use TMCms\Log\Entity\ErrorLogEntityRepository;

defined('INC') or exit;

BreadCrumbs::getInstance()
    ->addCrumb('Error log')
    ->addAction('Clear log', '?p='. P .'&do=_error_log_clear', ['confirm' => true])
;

// Check db exists
$log = new ErrorLogEntityRepository();
$log->addSimpleSelectFields(['id', 'ts', 'type', 'file', 'line']);
$log->addSimpleSelectFieldsAsString('INET_NTOA(`ip_long`) AS `ip`');
$log->addOrderByField('ts', true);

echo CmsTableHelper::outputTable([
    'data' => $log,
    'columns' => [
        'type' => [
            'narrow' => true,
        ],
        'ts' => [
            'title' => 'Date',
            'type' => 'datetime',
        ],
        'ip' => [],
        'file' => [],
        'line' => [],
    ],
    'view' => true,
]);