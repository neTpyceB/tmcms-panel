<?php

use TMCms\Admin\Structure\Entity\PageEntity;
use TMCms\Config\Settings;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\CmsTabs;
use TMCms\HTML\Cms\Element\CmsButton;
use TMCms\HTML\Cms\Element\CmsCheckbox;
use TMCms\HTML\Cms\Element\CmsInputText;
use TMCms\HTML\Cms\Element\CmsRow;
use TMCms\HTML\Cms\Element\CmsSelect;
use TMCms\HTML\Cms\Element\CmsTextarea;
use TMCms\HTML\Cms\Widget\Custom;
use TMCms\HTML\Cms\Widget\FileManager;
use TMCms\HTML\Cms\Widget\SitemapPages;
use TMCms\Routing\Languages;
use TMCms\Routing\Structure;

defined('INC') or exit;

if (Settings::get('locked_structure')) {
    error('Site structure can not be modified. Locked by Settings');
}

Structure::refreshTemplatesInDb();

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) return;
$id = (int)$_GET['id'];

$templates = array();
foreach (q_assoc_iterator('
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

$page_data = q_assoc_row('
SELECT
	`p`.`id`,
	`p`.`title`,
	`p`.`string_label`
FROM `cms_pages` AS `p`
WHERE `p`.`id` = "'. $id .'"
');

$pages = ['---'] + Structure::getPagesAsTreeForSelects();

$page = new PageEntity($id);

if (ctype_digit((string)$page->getRedirectUrl())) {
    $redirect_url = Structure::getPathById($page->getRedirectUrl());
    if ($redirect_url && $redirect_url != $page->getRedirectUrl()) {
        $page->setRedirectUrl($redirect_url);
    }
	unset($redirect_url);
}

$form1 = CmsForm::getInstance()
    ->addData($page)
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
        ->setHintText('Under which branch this Page will be located'))
    ->addField('Menu Title', CmsInputText::getInstance('title')
        ->validateRequired()
//        ->setHintText('Page title for browser heading')
    )
    ->outputTagForm(false)
;

if ($page->getPid()) {
    $form1->addField('Location', CmsInputText::getInstance('location')
        ->validateRequired()
        ->setHintText('Part of URL')
        ->setUid('title')
    );
} else {
    $languages = array_keys(Languages::getPairs());

    $form1->addField('Location', CmsSelect::getInstance('location')
        ->setOptions(array_combine($languages, $languages))
        ->setHintText('Part of URL')
        ->setUid('title')
    );
}

$form2 = CmsForm::getInstance()
    ->addData($page)
    ->disableFullView()
    ->addField('Title', CmsTextarea::getInstance('browser_title')->setHintText('Title for browser tab'))
    ->addField('Description', CmsTextarea::getInstance('description'))
    ->addField('Keywords', CmsTextarea::getInstance('keywords'))
    ->outputTagForm(false)
;

$form3 = CmsForm::getInstance()
    ->addData($page)
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
            ->path(DIR_FRONT_TEMPLATES_URL)
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
    ->outputTagForm(false)
;

$tabs = CmsTabs::getInstance()
    ->addTab(__('Main'), $form1)
    ->addTab(__('Meta'), $form2)
    ->addTab(__('Tech'), $form3)
;

$breadcrumbs = BreadCrumbs::getInstance()
    ->addCrumb('<a href="?p='. P .'">' . ucfirst(P) . '</a>')
    ->addCrumb(__('Edit Page'))
    ->addCrumb($page_data['title'])
;

foreach (\TMCms\Routing\Languages::getPairs() as $short => $full) {
    $lng_page_id = Structure::getIdByLabel($page->getStringLabel(), $short);
    if ($id == $lng_page_id) {
		continue; // Skip current opened page
	}
	if ($lng_page_id) {
        $breadcrumbs->addCrumb('[' . $short . ' version]', str_replace('&id=' . $id, '', SELF) . '&id=' . $lng_page_id);
	}
}

// Links to other language versions
foreach (\TMCms\Routing\Languages::getPairs() as $short => $full) {
    $lng_page_id = Structure::getIdByLabel($page_data['string_label'], $short);
    if ($page_data['id'] == $lng_page_id) {
        continue; // Skip current opened page
    }
    if ($lng_page_id) {
        $breadcrumbs->addAction(strtoupper($short) . ' version', str_replace('&id=' . $page_data['id'], '', SELF) . '&id='. $lng_page_id);
    }
}
// To custom components
$breadcrumbs->addAction('Custom Components', '?p=structure&do=customs&id=' . $page_data['id']);
// To components
$breadcrumbs->addAction('Components', '?p=structure&do=edit_components&id=' . $page_data['id']);

echo CmsForm::getInstance()
    ->enableAjax()
    ->setFormTitle('Edit page properties')
    ->addData($page)
    ->setAction('?p='. P .'&do=_edit_page&id='. $id)
    ->setSubmitButton(CmsButton::getInstance(__('Update')))
	->setCancelButton(CmsButton::getInstance(__('Cancel')))
    ->addField('', CmsRow::getInstance('form')
        ->setValue($tabs)
    )
;