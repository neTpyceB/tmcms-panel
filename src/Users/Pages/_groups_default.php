<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Users\Entity\AdminUserGroup;
use TMCms\Admin\Users\Entity\AdminUserGroupRepository;
use TMCms\Log\App;

$id = abs((int)$_GET['id']);

// Disable default on all groups
$groups_collection = new AdminUserGroupRepository;
$groups_collection->setDefault(0);
$groups_collection->save();

// Set default for selected group
$group = new AdminUserGroup($id);
$group->setDefault(1);
$group->save();

App::add('Group "' . $group->getTitle() . '" set as default');
Messages::sendGreenAlert('Default Group updated');

back();