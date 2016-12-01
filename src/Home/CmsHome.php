<?php

namespace TMCms\Admin\Home;

use TMCms\Files\Finder;
use TMCms\Traits\singletonInstanceTrait;

defined('INC') or exit;

Finder::getInstance()->addTranslationsSearchPath(__DIR__ . '/translations/');

class CmsHome
{
    use singletonInstanceTrait;

    public function _default()
    {
        require_once DIR_BASE . 'vendor/devp-eu/tmcms-admin/src/Home/Pages/' . __FUNCTION__ . '.php';
    }

    public function _ajax_users_log()
    {
        require_once DIR_BASE . 'vendor/devp-eu/tmcms-admin/src/Home/Pages/' . __FUNCTION__ . '.php';
    }

    public function _ajax_tools_application_log()
    {
        require_once DIR_BASE . 'vendor/devp-eu/tmcms-admin/src/Home/Pages/' . __FUNCTION__ . '.php';
    }

    public function _exit()
    {
        require_once DIR_BASE . 'vendor/devp-eu/tmcms-admin/src/Home/Pages/' . __FUNCTION__ . '.php';
    }

    public function _update_notes()
    {
        require_once DIR_BASE . 'vendor/devp-eu/tmcms-admin/src/Home/Pages/' . __FUNCTION__ . '.php';
    }

    public function _send_message_to_developers()
    {
        require_once DIR_BASE . 'vendor/devp-eu/tmcms-admin/src/Home/Pages/' . __FUNCTION__ . '.php';
    }

    public function _ajax_keep_admin_session()
    {
        ob_clean();
        die('1');
    }

    public function _ajax_get_notifications()
    {
        require_once DIR_BASE . 'vendor/devp-eu/tmcms-admin/src/Home/Pages/' . __FUNCTION__ . '.php';
    }
}