<?php
declare(strict_types=1);

// Ensure translation table auto-created
use TMCms\Admin\Structure\CmsStructure;
use TMCms\Admin\Structure\Entity\TranslationRepository;
use TMCms\HTML\BreadCrumbs;

$translations = new TranslationRepository();

BreadCrumbs::getInstance()
    ->addCrumb(ucfirst(P))
    ->addCrumb('Add language');

echo CmsStructure::getInstance()->_languages_add_edit_form();