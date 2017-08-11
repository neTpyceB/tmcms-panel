<?php
declare(strict_types=1);

$id = abs((int)$_GET['id']);
if (!$id) {
    return;
}

$group = new AdminUserGroup($id);

BreadCrumbs::getInstance()
    ->addCrumb(__('Edit Group'))
    ->addCrumb($group->getTitle());

$form = $this->_groups_form($group)
    ->setAction('?p=' . P . '&do=_groups_edit&id=' . $id . $id)
    ->setButtonSubmit('Update');

if (Users::getInstance()->getGroupData('can_set_permissions')) {
    $form->addField('Can set permissions', CmsCheckbox::getInstance('can_set_permissions')->setHintText('Can set permission to other groups'));
    $form->addField('Limited Filemanager', CmsCheckbox::getInstance('filemanager_limited')->setHintText('Limit Filemanager usage only to ' . DIR_PUBLIC_URL . ' folder'));
}

echo $form;