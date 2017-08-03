<?php

use TMCms\Config\Configuration;
use TMCms\Config\Settings;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\Element\CmsButton;
use TMCms\HTML\Cms\Element\CmsCheckbox;
use TMCms\HTML\Cms\Element\CmsHtml;
use TMCms\HTML\Cms\Element\CmsInputNumber;
use TMCms\HTML\Cms\Element\CmsInputText;
use TMCms\HTML\Cms\Element\CmsTextarea;

defined('INC') or exit;

$settings = Settings::getInstance()->init(true);
if (!is_array($settings)) {
    $settings = [];
}

if (isset($settings['allowed_ips'])) {
    $settings['allowed_ips'] = str_replace(',', "\n", $settings['allowed_ips']);
}

BreadCrumbs::getInstance()
    ->addCrumb(ucfirst(P))
    ->addCrumb('Technical Settings');


$form = CmsForm::getInstance()
    ->setAction('?p=' . P . '&do=_development')
    ->addData($settings)
    ->setSubmitButton(CmsButton::getInstance('Update'))
    ->addFieldBlock('Site',
        [
            ['name' => 'Production state', 'field' => CmsCheckbox::getInstance('production')->setHintText('Disables showing errors, disables db auto-checks, etc.')],
            ['name' => 'Visual edit', 'field' => CmsCheckbox::getInstance('enable_visual_edit')->setHintText('Enable visual edit mode on frontend')],
            ['name' => 'Components placeholders', 'field' => CmsCheckbox::getInstance('show_components_placeholders')->setHintText('Show keys instead of empty places for non-filled components')],
            ['name' => 'Allowed sizes for ImageProcessor', 'field' => CmsInputText::getInstance('image_processor_allowed_sizes')->setHintText('Coma-separated value pairs, eg 200x200,350x100,450x100')],
            ['name' => 'Allowed IPs', 'field' => CmsTextarea::getInstance('allowed_ips')->setHintText('Enter one IP on a line to set allowed IPs. Your IP is <a class="jsButton" onclick="document.getElementById(\'allowed_ips\').value += \'\r\n' . IP . '\'; return false;">' . IP . '</a>. Or leave empty to enable access to all')],
        ]
    )
    ->addFieldBlock('Optimization',
        [
            ['name' => 'Frontend DB cache', 'field' => CmsCheckbox::getInstance('common_cache')->setHintText('Caches database queries for pages, languages, translation variables, labels, etc.')],
            ['name' => 'Optimize HTML', 'field' => CmsCheckbox::getInstance('optimize_html')->setHintText('Removes spaces and comments')],
            ['name' => 'Use HTML file cache', 'field' => CmsCheckbox::getInstance('use_file_cache_for_all_pages')->setHintText('Cache entire page as is in one file for output')],
        ]
    )
    ->addFieldBlock('Middleware',
        [
            ['name' => 'Throttle limit', 'field' => CmsInputNumber::getInstance('middleware_throttle_limit')->setHintText('Limit of requests per minute from one client. Leave empty or set zero to disable.')],
        ]
    )
    ->addFieldBlock('Logs',
        [
            ['name' => 'Frontend log [<a href="?p=' . P . '&do=front_log">View</a>]', 'field' => CmsCheckbox::getInstance('save_frontend_log')->setHintText('Saves all front site request. Warning - use only in dev-mode, because it generates a lot of DB entries')],
            ['name' => 'Disable CMS access log', 'field' => CmsCheckbox::getInstance('disable_cms_access_log')->setHintText('Disables saving all CMS user actions in log')],
            ['name' => 'DB query analyzer [<a href="?p=' . P . '&do=analyze_db_queries">View</a>] ', 'field' => CmsCheckbox::getInstance('analyze_db_queries')->setHintText('Stores all queries for analyze, helps to debug and optimize queries')],
        ]
    )
    ->addFieldBlock('Debug',
        [
            ['name' => 'Show debug panel', 'field' => CmsCheckbox::getInstance('debug_panel')->setHintText('Shows load time, memory usage, DB queries, etc. Requires jQuery using $() function, and according action file in ' . DIR_FRONT_API_URL)],
            ['name' => 'Do not send JS errors', 'field' => CmsCheckbox::getInstance('do_not_send_js_errors')->setHintText('Prevents sending frontend JS errors to Developers')],
            ['name' => 'Do not send PHP errors', 'field' => CmsCheckbox::getInstance('do_not_send_php_errors')->setHintText('Prevents sending backend PHP errors to Developers')],
            ['name' => 'Do not log CMS usage', 'field' => CmsCheckbox::getInstance('do_not_log_cms_usage')->setHintText('Prevents saving system usage log')],
        ]
    )
    ->addFieldBlock('CMS',
        [
            ['name' => 'File size quota, in MB', 'field' => CmsInputText::getInstance('file_size_quota')->setHintText('Send notification when total file size exceeded quota. Leave empty or 0 to disable')],
            ['name' => 'Locked Structure', 'field' => CmsCheckbox::getInstance('locked_structure')->setHintText('Locks Structure pages. No one will be able to delete or activate\deactivate pages, modify page contents and properties, change order')],
            ['name' => 'Unique admin address', 'field' => CmsCheckbox::getInstance('unique_admin_address')->setHintText('Can access admin panel log-in form only using ' . DIR_CMS_URL . '?admin_key=' . Configuration::getInstance()->get('cms')['unique_key'])],
            ['name' => 'Do not expose Generator', 'field' => CmsCheckbox::getInstance('do_not_expose_generator')->setHintText('Disable showing in META tags that site generator is ' . CMS_NAME)],
            ['name' => 'Generate code', 'field' => CmsHtml::getInstance('cms_generate_code')->setValue('<a href="?p=' . P . '&do=code_generator">Go to code generator</a>')],
        ]
    );

echo $form;