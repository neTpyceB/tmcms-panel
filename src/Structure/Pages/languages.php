<?php
declare(strict_types=1);

use TMCms\Admin\Entity\LanguageEntityRepository;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTableHelper;

defined('INC') or exit;

BreadCrumbs::getInstance()
    ->addAction('Add Language', '?p=' . P . '&do=languages_add');

$languages = new LanguageEntityRepository();
$languages->addOrderByField('short');

echo CmsTableHelper::outputTable([
    'data'    => $languages,
    'columns' => [
        'short' => [
            'order' => true,
        ],
        'full'  => [
            'order' => true,
        ],
    ],
    'edit'    => true,
    'delete'  => true,
]);