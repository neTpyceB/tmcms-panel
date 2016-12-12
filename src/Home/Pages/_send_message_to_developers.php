<?php

defined('INC') or exit;

use TMCms\Admin\Messages;
use TMCms\Cache\Cacher;
use TMCms\Config\Configuration;
use TMCms\Log\App;
use TMCms\Network\Mailer;

Mailer::getInstance()
    ->setSubject('Message from ' . Configuration::getInstance()->get('site')['name'] . ' (' . CFG_DOMAIN . ')')
    ->setSender(Configuration::getInstance()->get('site')['email'], Configuration::getInstance()->get('site')['name'])
    ->setRecipient(CMS_SUPPORT_EMAIL, CMS_NAME)
    ->setMessage($_POST['message'])
    ->send()
;

Cacher::getInstance()->getDefaultCacher()->set('cms_home_support_email', NOW);

App::add('Message sent to developers');

Messages::sendGreenAlert('Message sent to developers');

back();