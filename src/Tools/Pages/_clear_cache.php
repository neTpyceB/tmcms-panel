<?php

use TMCms\Admin\Messages;
use TMCms\Cache\Cacher;
use TMCms\Log\App;

defined('INC') or exit;

// Remove entire Cache dir
Cacher::getInstance()->clearAllCaches();

App::add('Cache cleared');

Messages::sendGreenAlert('Cache cleared');

back();