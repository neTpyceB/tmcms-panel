<?php
declare(strict_types=1);

use TMCms\Admin\Updater;

$updater = Updater::getInstance();

if (isset($_GET['files'])) {
    // Update files
    $updater->updateSourceCode(); // Branch can be changed in config or supplied here as argument
}

if (isset($_GET['composer'])) {
    // Update libraries
    $updater->updateComposerVendors();
}

if (isset($_GET['db'])) {
    // Update database
    $updates = $updater->runMigrations();
}

// Output
$out = $updater->getResult();

if (isset($updates)) {
    $out['migrations'] = $updates;
}

dump($out, false, false);