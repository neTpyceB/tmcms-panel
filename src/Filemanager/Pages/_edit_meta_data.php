<?php

use TMCms\Admin\Filemanager\Entity\FilePropertyEntity;
use TMCms\Admin\Filemanager\Entity\FilePropertyEntityRepository;
use TMCms\Admin\Messages;
use TMCms\DB\SQL;
use TMCms\Log\App;

defined('INC') or exit;

$path = $_POST['path'];

if (isset($_POST['properties'])) {
    $data = $_POST['properties'];

    // Delete all before save
    /** @var FilePropertyEntity $properties */
    $properties = new FilePropertyEntityRepository;
    $properties->setWherePath($path);
    $properties->deleteObjectCollection();

    $table = $properties->getDbTableFields();

    // Field to be updated
    if (isset($data['update']) && is_array($data['update'])) {
        foreach ($data['update'] as $v) {
            $property = new FilePropertyEntity();
            $property->setKey($v['key']);
            $property->setPath($path);
            $property->setValue($v['value']);
            $property->save();
        }
    }

    // Create new fields
    if (isset($data['add']) && is_array($data['add'])) {
        foreach ($data['add'] as $v) {
            $property = new FilePropertyEntity();
            $property->setKey($v['key']);
            $property->setPath($path);
            $property->setValue($v['value']);
            $property->save();
        }
    }
}

App::add('Properties of file "' . $path . '" updated');
Messages::sendGreenAlert('Properties updated');

if (IS_AJAX_REQUEST) {
    die;
}