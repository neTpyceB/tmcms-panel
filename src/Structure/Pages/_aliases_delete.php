<?php

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageAliasEntity;
use TMCms\Log\App;
use TMCms\Routing\Structure;

defined('INC') or exit;


$alias = new PageAliasEntity($_GET['id']);
$alias->deleteObject();

App::add('Alias "' . $alias->getName() . '" deleted');
Messages::sendGreenAlert('Alias "' . $alias->getName() . '" deleted');

Structure::clearCache();
back();