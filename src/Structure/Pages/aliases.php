<?php

use TMCms\Admin\Structure\Entity\PageAliasEntityRepository;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTable;
use TMCms\HTML\Cms\CmsTableHelper;
use TMCms\HTML\Cms\Column\ColumnCheckbox;
use TMCms\HTML\Cms\Column\ColumnData;
use TMCms\HTML\Cms\Column\ColumnDelete;

defined('INC') or exit;

BreadCrumbs::getInstance()
    ->addNotes('Aliases are short aliases of site pages')
    ->addAction(__('Add Alias'), '?p=' . P . '&do=aliases_add');

$aliases = new PageAliasEntityRepository();
$aliases->addOrderByField('name');

echo CmsTableHelper::outputTable([
    'data'              => $aliases,
    'callback_function' => '\TMCms\Admin\Structure\CmsStructure::_aliases_callback',
    'columns'           => [
        'name'       => [
            'order' => true,
        ],
        'alias'      => [
            'html' => true,
        ],
        'href'       => [
            'title' => 'Links to',
            'html'  => true,
            'order' => true,
        ],
        'is_landing' => [
            'type'  => 'active',
            'hint'  => 'Use the alias when visitor came from search site and search matches to this keyword.',
            'order' => true,
        ],
    ],
    'edit'              => true,
    'delete'            => true,
]);