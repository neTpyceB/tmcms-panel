<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Files\FileSystem;

defined('INC') or exit;

foreach (FileSystem::scanDirs(DIR_BASE) as $f) {
    if (strpos($f['full'], '.git') !== false) {
        continue;
    }
    // Try to set all for everyone
    FileSystem::chmod($f['full'], 0777);
}

App::add('File permissions reset');
Messages::sendMessage('File permissions reset');

back();