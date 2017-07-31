<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Users\Entity\UserLogRepository;
use TMCms\Cache\Cacher;
use TMCms\Log\App;

$log = new UserLogRepository();
$log->deleteObjectCollection();

Cacher::getInstance()->getDefaultCacher()->delete('cms_tools_disk_and_db_usage');

App::add('Admin User log cleared');
Messages::sendGreenAlert('Log cleared');

back();