<?php
use TMCms\Admin\Messages;
use TMCms\Config\Settings;
use TMCms\Log\App;
use TMCms\Routing\Structure;

defined('INC') or exit;

if (!$_POST) return;;

$settings_obj = Settings::getInstance();

$settings = $settings_obj->init(true);
if (!is_array($settings)) {
    $settings = [];
}

$settings = array_merge($settings, $_POST);

$checkboxes = [
    'analyze_db_queries',
    'common_cache',
    'do_not_expose_generator',
    'do_not_log_cms_usage',
    'do_not_send_js_errors',
    'do_not_send_php_errors',
    'debug_panel',
    'enable_visual_edit',
    'locked_structure',
    'optimize_html',
    'production',
    'save_frontend_log',
    'save_back_access_log',
    'unique_admin_address',
    'use_file_cache_for_all_pages',
];

foreach ($checkboxes as $v) {
    if (!isset($_POST[$v])) $settings[$v] = 0;
}

// Allowed IPs
$_POST['allowed_ips'] = implode(',', array_unique(explode("\n", str_replace("\n\n", "\n", str_replace("\r", "\n", trim($_POST['allowed_ips']))))));

// Clear HTML cache if it is disabled
if (!isset($_POST['use_file_cache_for_all_pages'])) {
    Structure::clearCache();
}

$settings_obj->clear('', true);

foreach ($settings as $k => $v) $settings_obj->set($k, $v);

App::add('Settings updated');
Messages::sendGreenAlert('Settings updated');

back();