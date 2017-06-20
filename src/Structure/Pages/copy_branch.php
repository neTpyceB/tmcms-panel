<?php

use TMCms\Admin\Structure\Entity\PageEntity;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\Routing\Structure;

defined('INC') or exit;

$from_page = new PageEntity($_GET['from_id']);

BreadCrumbs::getInstance()
    ->addCrumb('<a href="?p=' . P . '">' . ucfirst(P) . '</a>')
    ->addCrumb('Copy structure branch')
    ->addCrumb($from_page->getTitle());

echo CmsFormHelper::outputForm($from_page->getDbTableName(), [
    'action' => '?p=' . P . '&do=_copy_branch',
    'button' => __('Copy'),
    'fields' => [
        'from_id'     => [
            'title' => 'From ID',
            'type'  => 'hidden',
            'value' => $from_page->getId(),
        ],
        'from_branch' => [
            'title' => 'From branch',
            'type'  => 'html',
            'value' => Structure::getPathById($from_page->getId()),
        ],
        'to_branch'   => [
            'title'  => 'To branch',
            'edit'   => 'pages',
            'hint'   => 'Leave empty to copy in root',
            'backup' => false,
        ],
    ],
]);