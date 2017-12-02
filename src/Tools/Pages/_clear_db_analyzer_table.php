<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\DB\SQL;
use TMCms\Log\App;

defined('INC') or exit;

if (SQL::getInstance()->tableExists('cms_db_queries_analyzer')) {
    q('TRUNCATE TABLE `cms_db_queries_analyzer`');
}
if (SQL::getInstance()->tableExists('cms_db_queries_analyzer_data')) {
    q('TRUNCATE TABLE `cms_db_queries_analyzer_data`');
}

App::add('DB Analyzer cleared');
Messages::sendGreenAlert('DB Analyzer cleared');

back();
