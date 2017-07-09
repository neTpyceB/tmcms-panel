<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Routing\Structure;

// Create word
Structure::addWord($_POST);

// Clear page caches
Structure::clearCache();

App::add('Word ' . $_POST['name'] . ' added');
Messages::sendGreenAlert('Word ' . $_POST['name'] . ' added');

go('?p=' . P . '&do=words');