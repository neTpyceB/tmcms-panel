<?php

use TMCms\Admin\Structure\CmsStructure;
use TMCms\HTML\BreadCrumbs;

defined('INC') or exit;

BreadCrumbs::getInstance()
    ->addCrumb('Add Domain');

echo CmsStructure::getInstance()->_domains_add_edit_form();