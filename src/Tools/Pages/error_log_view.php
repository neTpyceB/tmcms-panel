<?php
declare(strict_types=1);

use TMCms\Config\Constants;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\Log\Entity\ErrorLogEntity;

defined('INC') or exit;

$error = new ErrorLogEntity($_GET['id']);

BreadCrumbs::getInstance()
    ->addCrumb('View entry Nr. ' . $error->getId());

echo CmsFormHelper::outputForm([
    'data'   => $error,
    'fields' => [
        'type'  => [
            'type' => 'html',
        ],
        'date'  => [
            'type'  => 'datetime',
            'value' => date(Constants::FORMAT_CMS_DATETIME_FORMAT, $error->getTs()),
        ],
        'ip'    => [
            'type'  => 'datetime',
            'value' => long2ip((int)$error->getIpLong()),
        ],
        'agent' => [
            'type' => 'html',
        ],
        'file'  => [
            'type' => 'html',
        ],
        'line'  => [
            'type' => 'html',
        ],
        'msg'   => [
            'type'  => 'html',
            'title' => __('Message'),
        ],
        'vars'  => [
            'type'  => 'html',
            'title' => __('Variables'),
        ],
    ],
]);
