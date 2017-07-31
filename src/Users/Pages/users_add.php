<?php
declare(strict_types=1);

use TMCms\HTML\BreadCrumbs;

defined('INC') or exit;

BreadCrumbs::getInstance()
    ->addCrumb(__('Add User'));

echo $this->_users_add_edit_form();