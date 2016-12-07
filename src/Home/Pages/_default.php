<?php

defined('INC') or exit;

use TMCms\Admin\Menu;
use TMCms\Admin\Users;
use TMCms\Admin\Users\Entity\AdminUser;
use TMCms\Cache\Cacher;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\Dashboard;

Menu::getInstance()
    ->addHelpText('Notes are visible only to you')
    ->addHelpText('Logs show Access history and Application action history.')
    ->addHelpText('You can send message to Developers within this page.')
    ->setPageTitle('Dashboard')
    ->setPageDescription('Main page')
;

$user = new AdminUser(USER_ID);

BreadCrumbs::getInstance()
    ->addCrumb(__(ucfirst(P)))
    ->addCrumb(__('Main'))
    ->addNotes('Your note: ' . $user->getNotes())
    ->addAlerts('You are logged in as "'. $user->getLogin() .'", please log out if this is not you.')
;

echo \TMCms\HTML\Cms\CmsFormHelper::outputForm(NULL, [
    'action' => '?p=' . P . '&do=_update_notes',
    'ajax' => true,
    'button' => 'Update',
    'cancel' => 'Cancel',
    'fields' => [
        'notes' => [
            'edit' => 'wysiwyg',
            'hint' => __('These notes are visible only to you.'),
            'rows' => 10,
            'type' => 'textarea',
            'value' => $user->getNotes(),
        ],
    ],
    'title' => 'Private notes'
]);

$user = Users::getInstance();


if ($user->checkAccess('users', 'log')
    && $user->checkAccess('tools', 'application_log')
    && $user->checkAccess('home', '_ajax_users_log')
    && $user->checkAccess('home', '_ajax_tools_application_log')
) {
    echo Dashboard::getInstance('Logs', 2, 1, 'Logs')
        ->setCellProperties(0, 0, array(
            'width' => '50%'
        ))
        ->setCellProperties(1, 0, array(
            'width' => '50%'
        ))
        ->setCellValue(0, 0, '', '?p='. P .'&do=_ajax_users_log')
        ->setCellValue(1, 0, '', '?p='. P .'&do=_ajax_tools_application_log')
    ;
}

// Support form
$send = Cacher::getInstance()->getDefaultCacher()->get('cms_home_support_email');
if (!$send || NOW - $send > 60) {
    echo \TMCms\HTML\Cms\CmsFormHelper::outputForm(NULL, [
        'button' => __('Send message'),
        'collapsed' => true,
        'fields' => [
            'message' => [
                'type' => 'textarea'
            ],
        ],
        'title' => __('Send message to Cms Support'),
    ]);
}