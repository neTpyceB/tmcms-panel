<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Cache\Cacher;
use TMCms\Log\App;
use TMCms\Log\Entity\FrontLogEntityRepository;

defined('INC') or exit;

$log = new FrontLogEntityRepository();
$log->deleteObjectCollection();

// Clear db usage size in cache because it changes after log clear
Cacher::getInstance()->getDefaultCacher()->delete('cms_tools_disk_and_db_usage');

App::add('Front site log cleared');
Messages::sendMessage('Log cleared');

back();