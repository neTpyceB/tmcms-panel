<?php
declare(strict_types=1);

use TMCms\Admin\Entity\LanguageEntity;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsFormHelper;

defined('INC') or exit;

$language = new LanguageEntity($_GET['id']);

BreadCrumbs::getInstance()
    ->addCrumb(ucfirst(P))
    ->addCrumb('Edit Language')
    ->addCrumb($language->getShort() . ' [' . $language->getFull() . ']');

echo CmsFormHelper::outputForm($language->getDbTableName(), [
    'data'   => $language,
    'action' => '?p=' . P . '&do=_edit_language&id=' . $language->getId(),
    'button' => __('Update'),
    'fields' => [
        'short' => [
            'type'  => 'html',
            'title' => '2-letter code',
        ],
        'full'  => [
            'title' => 'Full name',
        ],
    ],
]);