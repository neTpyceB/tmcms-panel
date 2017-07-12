<?php

use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTableHelper;
use TMCms\Routing\Entity\PagesDomainEntityRepository;

defined('INC') or exit;

BreadCrumbs::getInstance()
    ->addCrumb('All Domains')
    ->addAction('Add Domain', '?p=' . P . '&do=domains_add')
    ->addNotes('Here you can add logic for multiple domains to work with one backend. Please create domain relations only if you need to restrict exact domain to exact languages. All domains without restrictions will work as usual with all features.');


$domains = new PagesDomainEntityRepository;

echo CmsTableHelper::outputTable([
    'data'    => $domains,
    'columns' => [
        'name' => [],
    ],
    'edit'    => true,
    'delete'  => true,
]);