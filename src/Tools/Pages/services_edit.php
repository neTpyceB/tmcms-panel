<?php
declare(strict_types=1);

use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\Services\Entity\ServiceEntity;

defined('INC') or exit;

$periods = [
    '60'     => '1 minute',
    '1800'   => '30 minutes',
    '3600'   => '1 hour',
    '86400'  => '24 hours',
    '604800' => '1 week',
];

$service = new ServiceEntity($_GET['id']);

BreadCrumbs::getInstance()
    ->addCrumb('Edit Service');

echo CmsFormHelper::outputForm([
    'data'   => $service,
    'action' => '?p=' . P . '&do=_services_edit&id=' . $service->getId(),
    'button' => __('Update'),
    'fields' => [
        'title'   => [],
        'period'  => [
            'options' => $periods,
        ],
        'autorun' => [
            'type' => 'checkbox',
        ],
    ],
]);