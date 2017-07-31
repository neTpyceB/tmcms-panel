<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Users\Entity\AdminUserGroup;
use TMCms\Log\App;

$group = new AdminUserGroup();
$group->loadDataFromArray($_POST);
$group->setCanSetPermissions((int)isset($_POST['can_set_permissions']));
$group->setFilemanagerLimited((int)isset($_POST['filemanager_limited']));
$group->save();

App::add('Group "' . $group->getTitle() . '" added');
Messages::sendGreenAlert('Group added');

go('?p=' . P . '&do=groups&highlight=' . $group->getId());