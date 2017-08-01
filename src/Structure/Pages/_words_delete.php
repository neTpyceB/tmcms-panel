<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Routing\Entity\PagesWordEntityRepository;
use TMCms\Routing\Languages;
use TMCms\Routing\Structure;

defined('INC') or exit;

if (!isset($_GET['id'])) {
    return;
}

foreach (Languages::getPairs() as $short => $full) {
    $words = new PagesWordEntityRepository;
    $words->setWhereName($_GET['id'] . '_' . $short);
    $words->deleteObjectCollection();
}

Structure::clearCache();

App::add('Word "' . $_GET['id'] . '" deleted');
Messages::sendGreenAlert('Word "' . $_GET['id'] . '" deleted');

back();