<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Files\Finder;
use \TMCms\Log\App;
use TMCms\Services\Entity\ServiceEntity;

defined('INC') or exit;

$_POST = sql_prepare($_POST);
$f =& $_POST['file'];

$exists = false;
foreach (Finder::getInstance()->getPathFolders(Finder::TYPE_SERVICES) as $folder) {
    $file = DIR_BASE . $folder . $f . '.php';
    if (file_exists($file)) {
        $exists = true;
    }
}

if (!$exists) {
    error('File not found');
}

$service = new ServiceEntity();
$service->loadDataFromArray([
    'title'      => $_POST['title'],
    'file'       => $f,
    'last_ts'    => NOW,
    'period'     => $_POST['period'],
    'auto_start' => (int)isset($_POST['auto_start']),
]);

$service->save();

App::add('Service "' . $service->getTitle() . '" added');
Messages::sendMessage('Service added');

go('?p=' . P . '&do=services');
