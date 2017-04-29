<?php

use TMCms\Admin\Structure\CmsStructure;
use TMCms\HTML\BreadCrumbs;
use TMCms\Routing\Entity\PagesDomainEntity;

defined('INC') or exit;

$domain = new PagesDomainEntity($_GET['id']);
$domain->setLanguages(json_decode($domain->getLanguages()));
$domain->setUrls(implode("\n", json_decode($domain->getUrls())));

BreadCrumbs::getInstance()
    ->addCrumb('Edit Domain')
    ->addCrumb($domain->getName());

echo CmsStructure::getInstance()->__domains_add_edit_form($domain)
    ->setAction('?p=' . P . '&do=_domains_edit&id=' . $domain->getId())
    ->setSubmitButton('Edit');