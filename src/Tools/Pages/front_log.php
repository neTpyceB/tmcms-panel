<?php
declare(strict_types=1);

use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTableHelper;
use TMCms\Log\Entity\FrontLogEntityRepository;

BreadCrumbs::getInstance()
    ->addCrumb('Frontend log')
    ->addAction(__('Clear log'), '?p=' . P . '&do=_front_log_clear', ['confirm' => true]);

$log = new FrontLogEntityRepository();
$log->addOrderByField('ts', true);

echo CmsTableHelper::outputTable([
    'data'    => $log,
    'columns' => [
        'level'        => [
            'html' => true,
        ],
        'ts'           => [
            'title' => 'Date',
            'type'  => 'datetime',
        ],
        'ip'           => [
            'title' => 'IP',
        ],
        'visitor_hash' => [],
        'text'         => [
            'cut_long_strings' => false,
        ],
    ],
    'filters' => [
        'ip'           => [
            'like' => true,
        ],
        'visitor_hash' => [
            'like' => true,
        ],
        'text'         => [
            'like' => true,
        ],
    ],
]);