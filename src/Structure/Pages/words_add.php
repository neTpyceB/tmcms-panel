<?php

use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\Routing\Entity\PagesWordEntityRepository;

defined('INC') or exit;

BreadCrumbs::getInstance()
    ->addCrumb(ucfirst(P), '?p=' . P)
    ->addCrumb(__('Add Word'));

$words = new PagesWordEntityRepository();

echo CmsFormHelper::outputForm([
    'title'  => __('Add Word'),
    'action' => '?p=' . P . '&do=_words_add',
    'button' => __('Add'),
    'cancel' => true,
    'fields' => [
        'name' => [
            'validate' => [
                'alphanumeric',
                'required',
            ],
        ],
        'word' => [
            'title' => 'Word',
            'translation' => true,
            'edit' => 'wysiwyg',
        ],
    ],
]);