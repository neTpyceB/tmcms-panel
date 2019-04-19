<?php
declare(strict_types=1);

use TMCms\Files\FileSystem;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTableHelper;
use TMCms\Services\Entity\ServiceEntityRepository;

defined('INC') or exit;

$periods = [
    '60'     => '1 minute',
    '1800'   => '30 minutes',
    '3600'   => '1 hour',
    '86400'  => '24 hours',
    '604800' => '1 week',
];

// Ensure db exists
$services = new ServiceEntityRepository();
$services->addOrderByField('title');
$services->getAsArrayOfObjects();

BreadCrumbs::getInstance()
    ->addAction('Add Service', '?p=' . P . '&do=services_add')
    ->addNotes('Run "php cms/index.php cms_is_running_background" in terminal to start services');

FileSystem::mkDir(DIR_FRONT_SERVICES);

echo CmsTableHelper::outputTable([
    'data'    => $services,
    'columns' => [
        'run'        => [
            'title' => 'Run/Stop',
            'value' => 'Trigger',
            'href'  => '?p=' . P . '&do=_services_run&id={%id%}',
        ],
        'title'      => [],
        'last_ts'    => [
            'type' => 'datetime',
        ],
        'period'     => [],
        'running'    => [
            'type' => 'done',
        ],
        'auto_start' => [
            'type' => 'done',
        ],
    ],
    'edit'    => true,
    'delete'  => true,
]);
