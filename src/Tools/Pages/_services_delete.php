<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Services\Entity\ServiceEntity;

$service = new ServiceEntity($_GET['id']);
$service->deleteObject();

App::add('Service "' . $service->getTitle() . '" deleted');
Messages::sendGreenAlert('Service "' . $service->getTitle() . '" deleted');

back();