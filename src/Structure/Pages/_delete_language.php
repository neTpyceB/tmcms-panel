<?php

use TMCms\Admin\Entity\LanguageEntity;
use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Routing\Entity\PagesWordEntityRepository;
use TMCms\Routing\Structure;

defined('INC') or exit;

$language = new LanguageEntity($_GET['id']);

// Check language exists
if (!$language->getShort()) {
    return;
}

// Delete words
$words = new PagesWordEntityRepository();
$words->addWhereFieldIsLike('name', '_' . $language->getShort(), true, false);
$words->deleteObjectCollection();

// Dlete language
$language->deleteObject();

// Remove language column from translation table
q('ALTER TABLE `cms_translations` DROP `' . $language->getShort() . '`');

Structure::clearCache();
App::add('Language "' . $language->getFull() . '" deleted');
Messages::sendGreenAlert('Language "' . $language->getFull() . '" deleted');

back();