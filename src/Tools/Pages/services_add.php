<?php
declare(strict_types=1);

use TMCms\Files\FileSystem;
use TMCms\Files\Finder;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\Services\Entity\ServiceEntityRepository;

defined('INC') or exit;

$periods = [
    '60'     => '1 minute',
    '1800'   => '30 minutes',
    '3600'   => '1 hour',
    '86400'  => '24 hours',
    '604800' => '1 week',
];

FileSystem::mkDir(DIR_FRONT_SERVICES);

$services = new ServiceEntityRepository();
$service_pairs = $services->getPairs('title', 'id');

$files = [];
foreach (Finder::getInstance()->getPathFolders(Finder::TYPE_SERVICES) as $folder) {
    foreach (array_diff(scandir(DIR_BASE . $folder, SCANDIR_SORT_NONE), ['.', '..']) as $v) {
        $v = substr($v, 0, -4);
        if (in_array($v, $service_pairs, true)) {
            $v .= ' (Already existing in DB)';
        }
        $files[$v] = $v;
    }
}

BreadCrumbs::getInstance()
    ->addCrumb('Add Service');

echo CmsFormHelper::outputForm([
    'action' => '?p=' . P . '&do=_services_add',
    'button' => __('Add'),
    'fields' => [
        'title'   => [],
        'file'    => [
            'options' => $files,
        ],
        'period'  => [
            'options' => $periods,
        ],
        'autorun' => [
            'type' => 'checkbox',
        ],
    ],
]);