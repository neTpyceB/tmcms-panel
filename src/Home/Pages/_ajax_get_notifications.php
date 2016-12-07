<?php

defined('INC') or exit;

// Delete all message more than 1 week
use TMCms\Admin\Users\Entity\UsersMessageEntityRepository;

$messages_collection = new UsersMessageEntityRepository();
$messages_collection->setWhereToUserId(USER_ID);
$messages_collection->setWhereFromUserId(0); // System only
$messages_collection->setWhereOld();
$messages_collection->deleteObjectCollection();

// Get actual
$messages_collection = new UsersMessageEntityRepository();
$messages_collection->setWhereSeen(0);
$messages_collection->setWhereToUserId(USER_ID);
$messages_collection->setLimit(3);
$messages_collection->addOrderByField('ts', true);

$data = [];

foreach ($messages_collection->getAsArrayOfObjects() as $msg) {
    /** @var \TMCms\Admin\Users\Entity\UsersMessageEntity $msg */
    $data[] = $msg->getAsArray();
    $msg->setSeen(1);
    $msg->save();
}

ob_start();
echo json_encode($data, JSON_FORCE_OBJECT);
echo ob_get_clean();

$messages_collection->setSeen(1);
$messages_collection->save();

die;