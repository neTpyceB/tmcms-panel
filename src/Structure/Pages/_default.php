<?php

use TMCms\Admin\Menu;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTableHelper;

defined('INC') or exit;

BreadCrumbs::getInstance()
    ->addCrumb(ucfirst(P))
    ->addCrumb('All pages');

Menu::getInstance()
    ->addHelpText('You can manage entire site Structure, add, edit, remove pages and it\'s components')
    ->addHelpText('Click right mouse button on page name to see possible actions');

BreadCrumbs::getInstance()
    ->addAction('Add page', '?p=' . P . '&do=add_page');

$context_menu = [
    [
        'title' => 'Add Subpage',
        'href'  => '?p=' . P . '&do=add_page&pid={%id%}',
    ],
    [
        'title' => 'Copy Page',
        'href'  => '?p=' . P . '&do=add_page&pid={%id%}&from_id={%id%}',
    ],
    [
        'title' => 'Copy Branch',
        'href'  => '?p=' . P . '&do=copy_branch&from_id={%id%}',
    ],
    [
        'title' => 'Edit Content',
        'href'  => '?p=' . P . '&do=edit_components&id={%id%}',
    ],
    [
        'title' => 'Content History',
        'href'  => '?p=' . P . '&do=page_history&id={%id%}',
    ],
    [
        'title' => 'Custom Components',
        'href'  => '?p=' . P . '&do=customs&id={%id%}',
    ],
    [
        'title' => 'Properties',
        'href'  => '?p=' . P . '&do=edit_page&id={%id%}',
    ],
    [
        'title' => 'Preview on site',
        'href'  => '?p=' . P . '&do=_view_page_on_frontend&id={%id%}',
        'popup' => true,
    ],
    [
        'title' => 'Clickmap',
        'href'  => '?p=' . P . '&do=_view_page_on_frontend&clickmap&id={%id%}',
        'popup' => true,
    ],
    [
        'title'   => 'Delete',
        'href'    => '?p=' . P . '&do=_delete_page&id={%id%}',
        'confirm' => true,
    ],
];

$sql = '
SELECT
	`p`.`id`,
	`p`.`pid`,
	`p`.`location`,
	`p`.`title`,
	`p`.`in_menu`,
	`p`.`active`
FROM `cms_pages` AS `p`
ORDER BY `p`.`order`
';

echo CmsTableHelper::outputTable([
    'callback_function' => '\TMCms\Admin\Structure\CmsStructure::_default_callback',
    'data'              => $sql,
    'context_menu'      => $context_menu,
    'columns'           => [
        'id'         => [
            'type'     => 'tree',
            'title'    => 'Title [location]',
            'key'      => 'id',
            'show_key' => 'title',
            'width'    => '99%',
        ],
        'view'       => [
            'narrow' => true,
            'html'   => true,
        ],
        'properties' => [
            'narrow' => true,
            'html'   => true,
        ],
        'order'      => [
            'type' => 'order',
            'href' => '?p=' . P . '&do=_order_page&id={%id%}',
        ],
        'in_menu'    => [
            'type'  => 'active',
            'title' => 'Menu',
            'href'  => '?p=' . P . '&do=_menu_page&id={%id%}',
        ],
        'active'     => [
            'type'  => 'active',
            'title' => 'Active',
            'href'  => '?p=' . P . '&do=_active_page&id={%id%}',
        ],
        'delete'     => [
            'type' => 'delete',
            'href' => '?p=' . P . '&do=_delete_page&id={%id%}',
        ],
    ],
]);