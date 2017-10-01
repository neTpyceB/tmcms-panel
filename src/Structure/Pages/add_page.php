<?php

use TMCms\Config\Settings;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\CmsTabs;
use TMCms\HTML\Cms\Element\CmsButton;
use TMCms\HTML\Cms\Element\CmsCheckbox;
use TMCms\HTML\Cms\Element\CmsCheckboxList;
use TMCms\HTML\Cms\Element\CmsInputText;
use TMCms\HTML\Cms\Element\CmsRow;
use TMCms\HTML\Cms\Element\CmsSelect;
use TMCms\HTML\Cms\Element\CmsTextarea;
use TMCms\HTML\Cms\Widget\Custom;
use TMCms\HTML\Cms\Widget\FileManager;
use TMCms\HTML\Cms\Widget\SitemapPages;
use TMCms\Routing\Languages;
use TMCms\Routing\Structure;
use TMCms\Strings\UID;

defined('INC') or exit;

if (Settings::get('locked_structure')) {
	error('Site structure can not be modified. Locked by Settings');
}

Structure::refreshTemplatesInDb();

BreadCrumbs::getInstance()
    ->addCrumb(ucfirst(P), '?p='. P)
    ->addCrumb(__('Add Page'))
;

$templates = [];
foreach (q_assoc('
SELECT
	`tm`.`id`,
	`tm`.`file`
FROM `cms_pages_templates` AS `tm`
ORDER BY `tm`.`file`
') as $v) {
	$templates[$v['id']] = ucfirst(str_replace(['/'], [' - '], $v['file']));
}
if (!$templates) {
	error('Please create at least one template.');
}

// If copy page from another
$data = [];
if (isset($_GET['from_id']) && ctype_digit((string)$_GET['from_id'])) {
    $data = q_assoc_row('SELECT * FROM `cms_pages` WHERE `id` = "'. $_GET['from_id'] .'"');
}

$pages = ['---'] + Structure::getPagesAsTreeForSelects();

$to_copy_lngs = Languages::getPairs();
unset($to_copy_lngs[LNG]);

$form1 = CmsForm::getInstance()
    ->addData($data)
    ->disableFullView()
    ->addField('Template', CmsSelect::getInstance('template_id')
        ->setOptions($templates)
        ->validateRequired()
        ->setHintText('Page Components will be shown according to selected Template')
    )
    ->addField('Parent', CmsSelect::getInstance('pid')
        ->disableCustomStyled()
        ->validateRequired()
        ->setOptions($pages)
        ->allowHtml()
        ->setWidget(Custom::getInstance()
            ->setModalPopupAjaxUrl('?p='. P .'&do=pages_parent_tree&nomenu&id=pid')
        )
        ->setSelected(isset($_GET['pid']) ? (int)$_GET['pid'] : '')
        ->setHintText('Under which branch this Page will be located')
    )
    ->addField('Menu Title', CmsInputText::getInstance('title')
        ->validateRequired()
//        ->setHintText('Page title for browser heading')
    )
    ->addField('Location', CmsInputText::getInstance('location')
        ->validateRequired()
        ->setHintText('Part of URL')
    )
    ->disableFormTagOutput();
if ($to_copy_lngs) {
    $form1
        ->addField('Duplicate', CmsCheckboxList::getInstance('duplicates')
            ->setCheckboxes($to_copy_lngs)
            ->setHintText('Duplicate new page in other languages, if branch is available')
        );
}
;

$form2 = CmsForm::getInstance()
    ->addData($data)
    ->disableFullView()
    ->addField('Browser Title', CmsInputText::getInstance('browser_title')->setHintText('Title for browser tab'))
    ->addField('META Description', CmsTextarea::getInstance('description'))
    ->addField('META Keywords', CmsTextarea::getInstance('keywords'))
    ->disableFormTagOutput()
;

$form3 = CmsForm::getInstance()
    ->addData($data)
    ->disableFullView()
    ->addField('Go level down', CmsCheckbox::getInstance('go_level_down')
        ->setHintText('Insensibly loads first page in this branch')
    )
    ->addField('Redirect URL', CmsInputText::getInstance('redirect_url')
        ->setWidget(new SitemapPages)
        ->setHintText('Redirect Page to another location. Type full href in case of external link, or choose from the Site pages')
    )
    ->addField('Use static HTML file', CmsInputText::getInstance('html_file')
        ->setWidget(FileManager::getInstance()
            ->setPath(DIR_FRONT_TEMPLATES_URL)
        )
        ->setHintText('Use HTML file as a template for Page')
    )
    ->addField('Transparent GET', CmsCheckbox::getInstance('transparent_get')
        ->setHintText('Used by Developers, transcribe URL parameters to $_GET parameters')
    )
    ->addField('String Label', CmsInputText::getInstance('string_label')
        ->validateAlphaNumeric()
        ->setHintText('Unique label to find the page in code. Used by Developers only')
    )
    ->addField('Menu name', CmsInputText::getInstance('menu_name')
        ->validateAlphaNumeric()
        ->setHintText('Menu identifier. Use "main" by default'))
    ->disableFormTagOutput()
;

$tabs = CmsTabs::getInstance()
    ->addTab(__('Main'), $form1)
    ->addTab(__('Meta'), $form2)
    ->addTab(__('Tech'), $form3)
;


echo CmsForm::getInstance()
    ->addData($data)
    ->setAction('?p='. P .'&do=_add_page')
    ->setButtonSubmit(CmsButton::getInstance('Add new Page'))
    ->setButtonCancel(CmsButton::getInstance(__('Cancel')))
    ->addField('', CmsRow::getInstance('form')
        ->setValue($tabs)
    )
;

UID::textToUidJs(true, ['title' => 'location'], 50, 0, true);