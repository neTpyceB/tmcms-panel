<?php

use TMCms\Admin\Structure\Entity\PageAliasEntity;
use TMCms\Admin\Structure\Entity\PageAliasEntityRepository;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsFormHelper;

defined('INC') or exit;

/** @var PageAliasEntity $alias */
$alias = PageAliasEntityRepository::findOneEntityByCriteria(['name' => $_GET['name']]);
if (!$alias) {
    return;
}

BreadCrumbs::getInstance()
    ->addCrumb(ucfirst(P), '?p=' . P)
    ->addCrumb('Edit Alias')
    ->addCrumb($alias->getName());

echo CmsFormHelper::outputForm($alias->getDbTableName(), [
    'data'   => $alias,
    'action' => '?p=' . P . '&do=_aliases_edit&id=' . $alias->getId(),
    'button' => __('Update'),
    'cancel' => true,
    'fields' => [
        'name'       => [],
        'href'       => [
            'edit' => 'pages',
            'hint' => 'For example, you can use short link "en-contacts" linking it to page "/en/about-us/company/contacts/"',
        ],
        'is_landing' => [
            'type' => 'checkbox',
            'hint' => 'Use the alias when visitor came from search site and search matches to this keyword.',
        ],
    ],
]);