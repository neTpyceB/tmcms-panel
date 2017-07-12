<?php
declare(strict_types=1);

use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsFormHelper;

defined('INC') or exit;

BreadCrumbs::getInstance()
    ->addCrumb('CMS Credentials')
;

echo CmsFormHelper::outputForm([
    'fields' => [
        'cms' => [
            'type' => 'html',
            'value' => CMS_NAME,
        ],
        'version' => [
            'type' => 'html',
            'value' => CMS_VERSION,
        ],
        'support_email' => [
            'title' => 'Support e-mail',
            'type' => 'html',
            'value' => '<a href="mailto:' . CMS_SUPPORT_EMAIL . '">' . CMS_SUPPORT_EMAIL . '</a>',
        ],
        'copyright' => [
            'type' => 'html',
            'value' => 'Copy or usage of CMS or it\'s non opensource code parts without permissions from Developers and owner Company (<a target="_blank" href="'. CMS_SITE .'">' . CMS_OWNER_COMPANY . '</a>) is prohibited.
        <br> Appropriate license agreements exist.',
        ],
    ],
]);