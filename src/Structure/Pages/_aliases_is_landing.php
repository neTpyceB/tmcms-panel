<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageAliasEntity;
use TMCms\Admin\Structure\Entity\PageAliasEntityRepository;
use TMCms\Log\App;

defined('INC') or exit;

/** @var PageAliasEntity $alias */
$alias = PageAliasEntityRepository::findOneEntityByCriteria([
    'name' => $_GET['name'],
]);

if (!$alias) {
    return;
}

$alias->flipBoolValue('is_landing');
$alias->save();

App::add('Alias ' . $alias->getName() . ' edited');
Messages::sendGreenAlert('Alias ' . $alias->getName() . ' edited');

back();