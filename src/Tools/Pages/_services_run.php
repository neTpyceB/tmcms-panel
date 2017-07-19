<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Modules\Services\Entity\ServiceEntity;
use TMCms\Services\ServiceManager;

defined('INC') or exit;

$service = new ServiceEntity($_GET['id']);

$running = $service->getRunning();
if ($running) {
    $service->setRunning(0)->save();
} else {
    ServiceManager::run($service->getId(), 1);
}

App::add('Service "'. $service->getTitle() .'" '. ($running ? 'stopped' : 'run'));
Messages::sendMessage('Service "'. $service->getTitle() .'" '. ($running ? 'stopped' : 'run'));

back();