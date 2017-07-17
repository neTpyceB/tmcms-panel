<?php
declare(strict_types=1);

use TMCms\Admin\Structure\Entity\PageComponentCustomEntityRepository;

$order_second = $_GET['order'] + ($_GET['direct'] === 'up' ? -1 : 1);

$swap_first_entries = new PageComponentCustomEntityRepository;
$swap_first_entries->setWhereComponent($_GET['component']);
$swap_first_entries->setWherePageId($_GET['page_id']);
$swap_first_entries->setWhereTab($_GET['tab']);
$swap_first_entries->setWhereOrder($_GET['order']);


if (!$swap_first_entries->hasAnyObjectInCollection()) {
    back();
}

$swap_second_entries = new PageComponentCustomEntityRepository;
$swap_second_entries->setWhereComponent($_GET['component']);
$swap_second_entries->setWherePageId($_GET['page_id']);
$swap_second_entries->setWhereTab($_GET['tab']);
$swap_second_entries->setWhereOrder($order_second);

if (!$swap_first_entries->hasAnyObjectInCollection()) {
    // Means we do not have any next or previous entry
    back();
}

$swap_first_entries->setOrder($order_second);
$swap_second_entries->setOrder($_GET['order']);

$swap_first_entries->save();
$swap_second_entries->save();

back();