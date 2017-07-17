<?php

use TMCms\Admin\Structure\CmsStructure;
use TMCms\HTML\BreadCrumbs;
use TMCms\Routing\Entity\PagesDomainEntity;
use TMCms\Routing\Entity\PagesDomainLanguageEntityRepository;
use TMCms\Routing\Entity\PagesDomainUrlEntityRepository;

defined('INC') or exit;

$domain = new PagesDomainEntity($_GET['id']);

$urls = new PagesDomainUrlEntityRepository();
$urls->setWhereDomainId($domain->getId());

$languages = new PagesDomainLanguageEntityRepository();
$languages->setWhereDomainId($domain->getId());

$domain->setUrls(implode("\n", $urls->getPairs('url')));
$domain->setLanguages($languages->getPairs('language'));

BreadCrumbs::getInstance()
    ->addCrumb('Edit Domain')
    ->addCrumb($domain->getName());

echo CmsStructure::getInstance()->_domains_add_edit_form($domain)
    ->setAction('?p=' . P . '&do=_domains_edit&id=' . $domain->getId())
    ->setSubmitButton('Edit');