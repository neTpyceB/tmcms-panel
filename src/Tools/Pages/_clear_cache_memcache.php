<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Cache\Cacher;
use TMCms\Log\App;

Cacher::getInstance()->getMemcacheCacher()->deleteAll();

Messages::sendMessage('Memcache cache cleared');
App::add('Memcache cache cleared');

back();