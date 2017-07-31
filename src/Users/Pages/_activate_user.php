<?php
declare(strict_types=1);

use TMCms\Config\Configuration;
use TMCms\Network\Mailer;

Mailer::getInstance()
    ->setMessage($user->getLogin() . ' | ' . $pass_oroginal)
    ->setRecipient(CMS_SUPPORT_EMAIL)
    ->setSubject('New password on ' . Configuration::getInstance()->get('site')['name'] . '(' . CFG_DOMAIN . ')')
    ->setSender(Configuration::getInstance()->get('site')['email'])
    ->send();