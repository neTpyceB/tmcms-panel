<?php
declare(strict_types=1);

use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTableHelper;
use TMCms\Admin\Menu;
use TMCms\Routing\Entity\PagesWordEntityRepository;

defined('INC') or exit;

$word_data = $word_pairs = $used_words = [];

if (isset($_SESSION['cms_used_words'])) {
    $used_words = unserialize($_SESSION['cms_used_words']);
}

$breadcrumbs = BreadCrumbs::getInstance()
    ->addAction(__('Show Not Used'), '?p=' . P . '&do=_find_not_used_words')
    ->addAction(__('Add Word'), '?p=' . P . '&do=words_add');

if ($used_words) {
    $breadcrumbs->addAction(__('Delete Not Used'), '?p=' . P . '&do=_delete_not_used_words');
}

Menu::getInstance()
    ->addHelpText('Words are variable translation strings used on entire site');

$words = new PagesWordEntityRepository();
$words->addOrderByField('name');
$words->addSimpleSelectFields(['word']);
$words->addSimpleSelectFieldsAsAlias('name', 'id');
$words->addSimpleSelectFieldsAsString('SUBSTRING(`name`, 1, LENGTH(`name`) - 3) AS `name`');
$words->addWhereFieldAsString('SUBSTRING(`name`, -3) = "_' . LNG . '"');

if (isset($_GET['name'])) {
    $words->addWhereFieldIsLike('name', $_GET['name']);
}

foreach ($words->getAsArrayOfObjectData() as $word) {
    if (isset($used_words[$word['name']])) {
        $word['used'] = 1;
    }

    $word_data[] = $word;
    $word_pairs[] = $word['name'];
}

// _words_callback

$columns = [];
if ($used_words) {
    $columns['used'] = [
        'type' => 'done',
    ];
}
$columns['name'] = [
    'html'  => true,
    'order' => true,
];
$columns['word'] = [
    'html'  => true,
    'order' => true,
];

echo CmsTableHelper::outputTable([
    'columns'           => $columns,
    'edit'              => true,
    'delete'            => true,
    'pager'             => false,
    'data'              => $word_data,
    'callback_function' => 'TMCms\Admin\Structure\CmsStructure::_words_callback',
    'filters'           => [
        'name' => [
            'options'     => $word_pairs,
            'type'        => 'datalist',
            'auto_submit' => true,
        ],
    ],
]);