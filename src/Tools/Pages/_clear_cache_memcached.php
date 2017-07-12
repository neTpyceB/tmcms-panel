<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Cache\Cacher;
use TMCms\Log\App;

Cacher::getInstance()->getMemcachedCacher()->deleteAll();

Messages::sendMessage('Memcached cache cleared');
App::add('Memcached cache cleared');

back();