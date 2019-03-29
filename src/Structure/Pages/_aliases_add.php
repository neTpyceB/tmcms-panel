<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageAliasEntity;
use TMCms\Admin\Structure\Entity\PageAliasEntityRepository;
use TMCms\Log\App;
use TMCms\Routing\Structure;

$_POST = sql_prepare($_POST);

$_POST['name'] = str_replace('/', '', $_POST['name']);
if (!$_POST['name'] || !$_POST['href']) back();

if (PageAliasEntityRepository::findOneEntityByCriteria(['name' => $_POST['name']])) {
    error('Link name exists.');
}

$id = Structure::getIdByPath($_POST['href']);
if (!$id) {
    error('Link page not found');
}

// Create alias
$alias = new PageAliasEntity();
$alias->loadDataFromArray([
    'name'       => $_POST['name'],
    'page_id'    => $id,
    'href'       => $_POST['href'],
    'is_landing' => (int)isset($_POST['is_landing']),
]);
$alias->save();

App::add('Alias to page "' . Structure::getPathById($id) . '" with name "' . $_POST['name'] . '" added');
Messages::sendGreenAlert('Alias updated');

// Clear page cache
Structure::clearCache();

go('?p=' . P . '&do=aliases&highlight=' . $alias->getId());
