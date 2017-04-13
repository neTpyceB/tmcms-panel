<?php

use TMCms\Admin\Filemanager\Entity\FilePropertyEntity;
use TMCms\Admin\Filemanager\Entity\FilePropertyEntityRepository;
use TMCms\HTML\Cms\CmsFormHelper;

defined('INC') or exit;

$path = $_GET['path'];

/** @var FilePropertyEntity $properties */
$properties = new FilePropertyEntityRepository;
$properties->setWherePath($path);

$data = [
    'properties' => $properties->getAsArrayOfObjectData(true),
];

echo CmsFormHelper::outputForm(NULL, [
    'action'        => '?p=' . P . '&do=_edit_meta_data&path=' . $path,
    'ajax'          => true,
    'data'          => $data,
    'ajax_callback' => '_.con.close();',
    'full'          => false,
    'fields'        => [
        'path'       => [
            'type' => 'hidden',
            'value' => $path
        ],
        'properties' => [
            'title'  => 'Properties',
            'type'   => 'input_table',
            'fields' => [
                'key'   => [],
                'value' => [],
            ],
            'add'    => true,
            'delete' => true,
        ],
    ],
    'button'        => __('Update'),
]);

if (IS_AJAX_REQUEST) {
    die;
}