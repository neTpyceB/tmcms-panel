<?php

use TMCms\HTML\BreadCrumbs;

defined('INC') or exit;

BreadCrumbs::getInstance()
    ->addCrumb(__('Add User'));

echo $this->__users_form();