<?php

use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Routing\Entity\PagesWordEntity;
use TMCms\Routing\Entity\PagesWordEntityRepository;
use TMCms\Routing\Languages;
use TMCms\Routing\Structure;

defined('INC') or exit;

$name = $_GET['name'];
if (!$name) {
    return;
}

$word = $_POST['word'];

foreach (Languages::getPairs() as $k => $language) {
    if (!isset($word[$k])) {
        // No word in this language
        continue;
    }

    // Find existing
    $existing_word = PagesWordEntityRepository::findOneEntityByCriteria([
        'name' => $name . '_' . $k,
    ]);

    // Or create new
    if (!$existing_word) {
        $existing_word = new PagesWordEntity();
        $existing_word->setName($name . '_' . $k);
    }

    // Update
    $existing_word->setWord($word[$k]);
    $existing_word->save();
}

Structure::clearCache();

App::add('Word "' . $name . '" updated');
Messages::sendGreenAlert('Word "' . $name . '" updated');

go('?p=' . P . '&do=words');