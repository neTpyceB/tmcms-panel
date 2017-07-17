<?php
declare(strict_types=1);

use TMCms\Admin\Entity\LanguageEntity;
use TMCms\Admin\Messages;
use TMCms\Admin\Structure\CmsStructure;
use TMCms\Admin\Structure\Entity\PageEntity;
use TMCms\Log\App;
use TMCms\Routing\Languages;
use TMCms\Routing\Structure;

$form = CmsStructure::getInstance()->_languages_add_edit_form();
$errors = $form->validateAndGetErrors($_POST);
if ($errors) {
    error('Error found in form data');
}

// Checks
$_POST = sql_prepare($_POST);
if (trim($_POST['full']) === '' || !preg_match('/^[a-zA-Z]{2}$/', $_POST['short'])) {
    error('All fields are required. Language must be 2-letter string');
}

// Check language exists with same code
if (Languages::getIdByShort($_POST['short'])) {
    error('Language exists');
}

// Create language
$language = new LanguageEntity();
$language->loadDataFromArray($_POST);
$language->save();

// Because language column already may present
@q('ALTER TABLE `cms_translations` ADD `' . $_POST['short'] . '` TEXT');

// Copy content
if (isset($_POST['copy_from']) && Languages::exists($_POST['copy_from'])) {
    $from_id = Structure::getIdByPath($_POST['copy_from']);
    $new_id = $this->copy_pages($from_id);

    $page = new PageEntity($new_id);
    $page->setLocation($_POST['short']);
    $page->save();

    // Copy translations
    if (isset($_POST['copy_translations'])) {
        q('UPDATE `cms_translations` SET `' . $_POST['short'] . '` = `' . $_POST['copy_from'] . '`');
    }
}

// Clear pages cache
Structure::clearCache();

App::add('Language "' . $_POST['full'] . '" added');
Messages::sendGreenAlert('Language "' . $_POST['full'] . '" added');

go('?p=' . P . '&do=languages');