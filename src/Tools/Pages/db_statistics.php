<?php

use TMCms\Config\Configuration;
use TMCms\DB\SQL;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\Columns;
use TMCms\HTML\Cms\Element\CmsHtml;
use TMCms\Strings\Converter;

defined('INC') or exit;

$tables = SQL::getTables(Configuration::getInstance()->get('db')['name']);

$res = [
	'rows' => 0,
	'size' => 0,
	'index' => 0
];


foreach ($tables as $table) {
	$q = q_assoc_row("SHOW TABLE STATUS LIKE '". $table ."'");
	$res['rows'] += $q['Rows'];
	$res['size'] += $q['Data_length'] + $q['Index_length'];
	$res['index'] += $q['Index_length'];
}

$res['tables'] = count($tables);
$res['index'] = Converter::formatDataSizeFromBytes($res['index']);
$res['size'] = Converter::formatDataSizeFromBytes($res['size']);
$res['ratio'] = $res['size'] / $res['index'] < 25 ? 'Normal proportion' : 'Index seems to be too small';

BreadCrumbs::getInstance()
    ->addCrumb('Database usage statistics')
;

echo CmsForm::getInstance()
	->addData($res)
	->addField('Tables', CmsHtml::getInstance('tables'))
	->addField('Rows', CmsHtml::getInstance('rows'))
	->addField('Index size', CmsHtml::getInstance('index'))
	->addField('Database size', CmsHtml::getInstance('size'))
	->addField('Database / Index', CmsHtml::getInstance('ratio'))
;

echo '<div>Details</div>';

$form = CmsForm::getInstance();

foreach (q_assoc("SHOW STATUS") as $q) {
    $form->addField(str_replace('_', ' ', $q['Variable_name']), CmsHtml::getInstance($q['Variable_name'])->setValue($q['Value']));
}

echo $form;