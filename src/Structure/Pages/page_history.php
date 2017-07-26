<?php
declare(strict_types=1);

use TMCms\Admin\Structure\Entity\PageComponentHistoryRepository;
use TMCms\Admin\Structure\Entity\PageEntity;
use TMCms\Admin\Users\Entity\AdminUserRepository;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTable;
use TMCms\HTML\Cms\CmsTableHelper;
use TMCms\HTML\Cms\Column\ColumnData;
use TMCms\Routing\Structure;

defined('INC') or exit;

$page = new PageEntity($_GET['id']);

$pages_components_history = new PageComponentHistoryRepository();
$users = new AdminUserRepository();

$pages_components_history->addSimpleSelectFields(['id', 'version', 'ts']);
$users->addSimpleSelectFieldsAsString('CONCAT(`' . $users->getDbTableName() . '`.`name`, " ", `' . $users->getDbTableName() . '`.`surname`) AS `user`');

$pages_components_history->setWherePageId($page->getId());

$pages_components_history->mergeWithCollection($users, 'user_id');
$pages_components_history->addGroupBy('version');

BreadCrumbs::getInstance()
    ->addCrumb('Page content history')
    ->addCrumb($page->getTitle());

echo CmsTableHelper::outputTable([
    'data'    => $pages_components_history,
    'pager'   => false,
    'columns' => [
        'version'      => [
            'narrow' => true,
        ],
        'ts'           => [
            'title' => 'Date',
            'type'  => 'datetime',
        ],
        'user'         => [],
        'view_on_site' => [
            'title'           => 'View on site',
            'value'           => 'View',
            'href_new_window' => true,
            'href'            => Structure::getPathById($page->getId()) . '?cms_content_version={%version%}',
        ],
        'restore'      => [
            'value' => 'Restore',
            'href'  => '?p=' . P . '&do=_restore_page_history&version={%version%}&id=' . $page->getId(),
        ],
    ],
]);