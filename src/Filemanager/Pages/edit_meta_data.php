<?php

use TMCms\Admin\Filemanager\Entity\FilePropertyEntity;
use TMCms\Admin\Filemanager\Entity\FilePropertyEntityRepository;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\Routing\Languages;

defined('INC') or exit;

$path = $_GET['path'];

/** @var FilePropertyEntity $properties */
$properties = new FilePropertyEntityRepository;
$properties->setWherePath($path);
$properties = $properties->getAsArrayOfObjectData(true);

// Predefined alt columns for images
if (!$properties) {
    $is_image = in_array(pathinfo($path, PATHINFO_EXTENSION), ['jpg', 'png', 'bmp', 'gif']);
    if ($is_image) {
        $i = 0;
        foreach (Languages::getPairs() as $short => $full) {
            $properties[] = [
                'id'    => ++$i,
                'key'   => 'alt_' . $short,
                'value' => '',
            ];
        }
    }
}

$data = [
    'properties' => $properties,
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

die;