<?php
declare(strict_types=1);

use TMCms\Admin\Structure\Entity\PageAliasEntityRepository;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsFormHelper;

defined('INC') or exit;

BreadCrumbs::getInstance()
    ->addCrumb('Add Alias');

$aliases = new PageAliasEntityRepository();

echo CmsFormHelper::outputForm($aliases->getDbTableName(), [
    'action' => '?p=' . P . '&do=_aliases_add',
    'button' => __('Add'),
    'cancel' => true,
    'fields' => [
        'name'       => [
            'title' => 'Short link',
        ],
        'href'       => [
            'title' => 'Link to page',
            'edit'  => 'pages',
            'hint'  => 'For example, you can use short link "en-contacts" linking it to page "/en/about-us/company/contacts/"',
        ],
        'is_landing' => [
            'type' => 'checkbox',
            'hint' => 'Use the alias when visitor came from search site and search matches to this keyword.',
        ],
    ],
]);