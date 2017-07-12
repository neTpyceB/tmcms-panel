<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Routing\Structure;

defined('INC') or exit;

Structure::generateStructureXml();

Messages::sendMessage('Structure XML generated');
App::add('Structure XML generated from admin panel');

back();