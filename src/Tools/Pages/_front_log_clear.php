<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Cache\Cacher;
use TMCms\Log\App;
use TMCms\Log\Entity\ErrorLogEntityRepository;

$error_log = new ErrorLogEntityRepository();
$error_log->deleteObjectCollection();

Cacher::getInstance()->getDefaultCacher()->delete('cms_tools_disk_and_db_usage');

App::add('Error log cleared');
Messages::sendGreenAlert('Log cleared');

back();