<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Cache\Cacher;
use TMCms\Log\App;

defined('INC') or exit;

// Clear file cache
Cacher::getInstance()->getFileCacher()->deleteAll();

Messages::sendMessage('File cache cleared');
App::add('File cache cleared');

back();