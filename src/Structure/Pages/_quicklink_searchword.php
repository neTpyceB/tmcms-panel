<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageQuicklinkEntityRepository;

defined('INC') or exit;

$ql = PageQuicklinkEntityRepository::findOneEntityByCriteria([
    'name' => $_GET['name'],
]);

if (!$ql) {
    return;
}

$ql->flipBoolValue('searchword');
$ql->save();

Messages::sendGreenAlert('Quicklink updated');

back();