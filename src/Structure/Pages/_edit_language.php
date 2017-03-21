<?php

use TMCms\Admin\Entity\LanguageEntity;
use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Routing\Structure;

defined('INC') or exit;

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    return;
}
$id = (int)$_GET['id'];
if (!$id) {
    return;
}

$language = new LanguageEntity($id);
$language->setFull($_POST['full']);
$language->save();

Structure::clearCache();

App::add('Language "' . $language->getShort() . '" updated');
Messages::sendMessage('Language "' . $language->getShort() . '" updated');

go('?p=' . P . '&do=languages');