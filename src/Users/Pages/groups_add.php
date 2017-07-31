<?php
declare(strict_types=1);

use TMCms\Admin\Users;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\Element\CmsCheckbox;

BreadCrumbs::getInstance()
    ->addCrumb(__('Add Group'));

$form = $this->_groups_form();

if (Users::getInstance()->getGroupData('can_set_permissions')) {
    $form->addField('Can set permissions', CmsCheckbox::getInstance('can_set_permissions')->setHintText('Can set permission to other groups'));
    $form->addField('Limited Filemanager', CmsCheckbox::getInstance('filemanager_limited')->setHintText('Limit Filemanager usage only to ' . DIR_PUBLIC_URL . ' folder'));
}

echo $form;