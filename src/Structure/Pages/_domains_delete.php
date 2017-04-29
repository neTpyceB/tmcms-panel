<?php

use TMCms\Admin\Entity\LanguageEntity;
use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Routing\Entity\PagesDomainEntity;
use TMCms\Routing\Structure;

defined('INC') or exit;

$domain = new PagesDomainEntity($_GET['id']);
$domain->deleteObject();

Structure::clearCache();

App::add('Domain "' . $domain->getName() . '" removed');
Messages::sendGreenAlert('Domain "' . $domain->getName() . '" removed');

back();