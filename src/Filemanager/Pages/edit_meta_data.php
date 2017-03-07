<?php

use TMCms\Admin\Filemanager\Entity\FileMetaEntity;
use TMCms\Admin\Filemanager\Entity\FileMetaEntityRepository;
use TMCms\HTML\Cms\CmsFormHelper;

defined('INC') or exit;

$path = $_GET['path'];

/** @var FileMetaEntity $meta */
$meta = FileMetaEntityRepository::findOneEntityByCriteria([
    'path' => $path,
]);

echo CmsFormHelper::outputForm(NULL, [
    'action' => '?p=' . P . '&do=_edit_meta_data&path=' . $path,
    'ajax' => true,
    'ajax_callback' => '_.con.close();',
    'full' => false,
    'fields' => [
        'path' => [
            'type' => 'hidden',
            'value' => $path
        ],
        'data' => [
            'title' => __('Meta data'),
            'value' => $meta ? $meta->getData() : '',
        ],
    ],
    'button' => __('Update'),
]);

if (IS_AJAX_REQUEST) {
    die;
}