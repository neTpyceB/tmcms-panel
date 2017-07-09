<?php
declare(strict_types=1);

use TMCms\Admin\Structure\Entity\PageComponentCustomEntity;
use TMCms\Admin\Structure\Entity\PageComponentCustomEntityRepository;

$all_entries = new PageComponentCustomEntityRepository;
$all_entries->setWhereComponent($_GET['component']);
$all_entries->setWhereTab($_GET['tab']);
$all_entries->setWherePageId($_GET['page_id']);
$all_entries->addOrderByField();

// Delete selected fields
$delete_entries = clone $all_entries;
$delete_entries->setWhereOrder($_GET['order']);
$delete_entries->deleteObjectCollection();

// Remove deleted entry from all entries
$all_entries->addWhereFieldIsNot('order', $_GET['order']);

// Re-save order for all elements
/** @var PageComponentCustomEntity $entry */
foreach ($all_entries->getAsArrayOfObjects() as $entry) {
    if ($entry->getOrder() > $_GET['order']) {
        $entry->setOrder($entry->getOrder() - 1);
        $entry->save();
    }
}

back();