<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Cache\Cacher;
use TMCms\Log\App;

defined('INC') or exit;

$last_ts = 0;
if (file_exists(DIR_BASE . '/sitemap.gz')) {
    $url = 'http://www.google.com/ping?sitemap=http://' . CFG_DOMAIN . '/sitemap.xml';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);

    $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ('200' === $return_code) {
        $last_ts = NOW;
    }
}

Cacher::getInstance()->getDefaultCacher()->set('cms_tools_submit_structure_xml', $last_ts);

App::add('XML Structure submitted to Google');
Messages::sendGreenAlert('XML Structure submitted to Google');

back();