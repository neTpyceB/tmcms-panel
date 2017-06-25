<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageAliasEntity;
use TMCms\Log\App;

defined('INC') or exit;

/** @var PageAliasEntity $alias */
$alias = new PageAliasEntity($_GET['id']);
$alias->flipBoolValue('is_landing');
$alias->save();

App::add('Alias ' . $alias->getName() . ' edited');
Messages::sendGreenAlert('Alias ' . $alias->getName() . ' edited');

die;