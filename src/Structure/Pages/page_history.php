<?php
declare(strict_types=1);

use TMCms\Admin\Structure\Entity\PageEntity;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTableHelper;
use TMCms\Routing\Structure;

defined('INC') or exit;

$page = new PageEntity($_GET['id']);

$pages_components_history_sql = 'SELECT `cms_pages_components_history`.`id`, `cms_pages_components_history`.`version`, `cms_pages_components_history`.`ts`, CONCAT(`cms_users`.`name`, " ", `cms_users`.`surname`) AS `user` FROM `cms_pages_components_history` INNER JOIN `cms_users` AS `cms_users` ON (`cms_users`.`id` = `cms_pages_components_history`.`user_id`) WHERE `cms_pages_components_history`.`page_id` = "'. $page->getId() .'" GROUP BY `cms_pages_components_history`.`version` ORDER BY `cms_pages_components_history`.`ts` DESC';

BreadCrumbs::getInstance()
    ->addCrumb('Page content history')
    ->addCrumb($page->getTitle());

echo CmsTableHelper::outputTable([
    'data'    => $pages_components_history_sql,
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
