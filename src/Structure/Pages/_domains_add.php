<?php

use TMCms\Admin\Entity\LanguageEntity;
use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Routing\Entity\PagesDomainEntity;
use TMCms\Routing\Structure;

defined('INC') or exit;

$_POST['urls'] = json_encode(explode("\n", $_POST['urls']));
$_POST['languages'] = json_encode($_POST['languages']);

$domain = new PagesDomainEntity();
$domain->loadDataFromArray($_POST);
$domain->save();

Structure::clearCache();

App::add('Domain "' . $domain->getName() . '" added');
Messages::sendGreenAlert('Domain "' . $domain->getName() . '" added');

go('?p=' . P . '&do=domains&highlight=' . $domain->getId());