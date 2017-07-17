<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Cache\Cacher;
use TMCms\Log\App;
use TMCms\Network\HttpProtocol;

defined('INC') or exit;

$last_ts = 0;
if (file_exists(DIR_BASE . '/sitemap.gz') && HttpProtocol::is_status_200(HttpProtocol::extract_headers(HttpProtocol::get('http://www.google.com/ping?sitemap=http://' . CFG_DOMAIN . '/sitemap.xml')))) {
    $last_ts = NOW;
}

Cacher::getInstance()->getDefaultCacher()->set('cms_tools_submit_structure_xml', $last_ts);

App::add('XML Structure submitted to Google');
Messages::sendGreenAlert('XML Structure submitted to Google');

back();