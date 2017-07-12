<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Log\App;

defined('INC') or exit;

// Create simple file if not exists already
if (!file_exists(DIR_BASE .'robots.txt')) {
	file_put_contents(DIR_BASE .'robots.txt', 'User-agent: *'. "\n" .'Allow: /'. "\n" .'Host: ' . CFG_DOMAIN);
}

Messages::sendMessage('File robots.txt created');
App::add('File robots.txt created');

back();