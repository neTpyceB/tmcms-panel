<?php

use TMCms\Admin\Entity\LanguageEntity;
use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Routing\Entity\PagesDomainEntity;
use TMCms\Routing\Structure;

defined('INC') or exit;

$urls = explode("\n", $_POST['urls']);
foreach ($urls as & $v) {
    $v = trim($v);
}

$_POST['urls'] = json_encode($urls);
$_POST['languages'] = json_encode($_POST['languages']);

$domain = new PagesDomainEntity($_GET['id']);
$domain->loadDataFromArray($_POST);
$domain->save();

Structure::clearCache();

App::add('Domain "' . $domain->getName() . '" updated');
Messages::sendGreenAlert('Domain "' . $domain->getName() . '" updated');

go('?p=' . P . '&do=domains&highlight=' . $domain->getId());