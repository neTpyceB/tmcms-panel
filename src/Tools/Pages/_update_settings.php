<?php

use TMCms\Admin\Messages;
use TMCms\Config\Settings;
use TMCms\Log\App;

defined('INC') or exit;

if (!$_POST) {
    return;
}
$settings_obj = Settings::getInstance();

// Get all current settings
$settings = array_merge($settings_obj->init(true), $_POST);

// Checkboxes on page that require update
$checkboxes = [
    'admin_panel_on_site',
    'allow_registration',
    'disable_cms_translations',
    'show_language_selector_flags',
    'autogenerate_sitemap_xml',
    'clickmap',
    'error_404_convert_transparent_get',
    'guess_broken_path',
    'error_404_find_in_structure',
    'error_404_go_to_main',
    'error_404_show_default_page',
    'skip_lng_in_generated_links',
    'skip_lng_redirect_to_same_page',
    'lng_by_session',
    'lng_by_cookie',
    'lng_by_http_header',
    'lng_by_ip',
    'permanent_redirects',
    'save_location_change_history',
];

// Updated items
foreach ($checkboxes as $v) {
    if (!isset($_POST[$v])) {
        $settings[$v] = 0;
    }
}

// Delete all
$settings_obj->clear('', true);

foreach ($settings as $k => $v) {
    if (!$k) {
        continue;
    }

    // Save all items
    $settings_obj->set($k, $v);
}

App::add('Settings updated');
Messages::sendGreenAlert('Settings updated');

back();