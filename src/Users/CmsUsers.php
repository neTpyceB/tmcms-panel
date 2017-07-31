<?php
declare(strict_types=1);

namespace TMCms\Admin\Users;

use TMCms\Admin\AdminLanguages;
use TMCms\Admin\Users;
use TMCms\Admin\Users\Entity\AdminUser;
use TMCms\Files\Finder;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\Modules\IModule;
use TMCms\Traits\singletonInstanceTrait;

defined('INC') or exit;

Finder::getInstance()->addTranslationsSearchPath(__DIR__ . '/translations/');

BreadCrumbs::getInstance()
    ->addCrumb(__(ucfirst(P)), '?p=' . P);

class CmsUsers implements IModule
{
    use singletonInstanceTrait;

    public function _default()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function users_add()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function edit()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    /**
     * Form for Add and Edit Users
     *
     * @param array $data
     *
     * @return CmsForm
     */
    public function _users_add_edit_form(array $data = []): CmsForm
    {
        $form_array = [
            'title'        => $data ? __('Edit user') : __('Add user'),
            'data'         => $data,
            'action'       => '?p=' . P . '&do=_users_add',
            'button'       => 'Add',
            'save_in_file' => true,
            'fields'       => [
                'group_id' => [
                    'name'    => 'Group',
                    'options' => Users::getInstance()->getGroupsPairs(),
                ],
                'lng'      => [
                    'name'    => 'Preferred language',
                    'options' => AdminLanguages::getPairs(),
                ],
                'style'    => [
                    'name'    => 'Preferred style',
                    'options' => [
                        'original' => 'Original',
                    ],
                ],
                'login',
                'password' => [
                    'type' => 'random',
                ],
                'active'   => [
                    'type' => 'checkbox',
                ],
                'name',
                'surname',
                'phone',
                'avatar'   => [
                    'edit' => 'files',
                    'path' => DIR_IMAGES_URL . 'avatars',
                ],
                'email'    => [
                    'type' => 'email',
                ],
                'comments' => [
                    'type' => 'textarea',
                ],
            ],
        ];

        // Can not edit login
        if ($data) {
            $form_array['unset'] = [
                'login',
            ];
        }

        return CmsFormHelper::outputForm('cms_users',
            $form_array
        );
    }

    public function _users_add()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _edit()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _active()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _multiple_active()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _multiple_delete()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _multiple_export()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _delete()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function groups()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function groups_add()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _groups_add()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function groups_edit()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    /**
     * Form for Add and Edit Users
     *
     * @param array $data
     *
     * @return CmsForm
     */
    public function _groups_form(array $data = []): CmsForm
    {
        $form_array = [
            'title'  => $data ? __('Edit group') : __('Add group'),
            'data'   => $data,
            'action' => '?p=' . P . '&do=_groups_add',
            'button' => 'Add',
            'fields' => [
                'title',
            ],
        ];

        return CmsFormHelper::outputForm('cms_groups',
            $form_array
        );
    }

    public function _groups_edit()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _groups_default()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _groups_delete()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function groups_permissions()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _groups_permissions()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function sessions()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _kick()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function log()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function chat()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _ajax_send_message()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _ajax_get_messages()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _change_lng()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    private function _activate_user(AdminUser $user, $pass_oroginal)
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }
}