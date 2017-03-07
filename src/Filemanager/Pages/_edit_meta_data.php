<?php

use TMCms\Admin\Filemanager\Entity\FileMetaEntity;
use TMCms\Admin\Filemanager\Entity\FileMetaEntityRepository;
use TMCms\Admin\Messages;
use TMCms\Log\App;

defined('INC') or exit;

$path = $_POST['path'];

/** @var FileMetaEntity $meta */
$meta = FileMetaEntityRepository::findOneEntityByCriteria([
    'path' => $path,
]);

if (!$meta) {
    $meta = new FileMetaEntity();
    $meta->setPath($path);
}

$meta->setData($_POST['data']);
$meta->save();

App::add('Meta of file "' . $path . '" updated');
Messages::sendGreenAlert('File meta updated');

if (IS_AJAX_REQUEST) {
    die;
}