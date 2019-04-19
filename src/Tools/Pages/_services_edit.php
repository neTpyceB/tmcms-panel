<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Services\Entity\ServiceEntity;

defined('INC') or exit;

$service = new ServiceEntity($_GET['id']);
$service->loadDataFromArray($_POST);
$service->setAutoStart((int)isset($_POST['auto_start']));
$service->save();

App::add('Service "'. $service->getTitle() .'" edited');
Messages::sendMessage('Service "'. $service->getTitle() .'" updated');

go('?p='. P .'&do=services');
