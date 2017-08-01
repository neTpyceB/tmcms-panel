<?php
declare(strict_types=1);

use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\Routing\Entity\PagesWordEntityRepository;
use TMCms\Routing\Languages;

defined('INC') or exit;

$languages = Languages::getPairs();
if (!$languages) {
    error('No languages.');
}

// Id is the name
$name = sql_prepare($_GET['id']);
$data = [
    'name' => $name,
    'word' => [],
];

// Select existing data
$names = [];
foreach ($languages as $k => $v) {
    $names[] = $name . '_' . $k;
}
if ($names) {
    foreach (q_assoc_iterator('SELECT `name`, `word` FROM `cms_pages_words` WHERE `name` IN ("' . implode('","', $names) . '")') AS $v) {
        $data['word'][substr($v['name'], -2)] = $v['word'];
    }
}

BreadCrumbs::getInstance()
    ->addCrumb('Edit Word')
    ->addCrumb($data['name']);

$words = new PagesWordEntityRepository();

echo CmsFormHelper::outputForm([
    'ajax'   => true,
    'action' => '?p=' . P . '&do=_words_edit&name=' . $name,
    'button' => __('Save'),
    'cancel' => true,
    'data'   => $data,
    'fields' => [
        'word' => [
            'translation' => true,
            'edit'        => 'wysiwyg',
        ],
    ],
]);