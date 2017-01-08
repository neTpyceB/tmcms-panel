<?php

defined('INC') or exit;

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    return;
}
$id = (int)$_GET['id'];

use TMCms\Admin\Messages;
use TMCms\Admin\Structure\Entity\PageComponentCustomEntity;
use TMCms\Admin\Structure\Entity\PageComponentCustomEntityRepository;
use TMCms\Log\App;
use TMCms\Routing\Structure;

// Delete all current data
$page_components = new PageComponentCustomEntityRepository();
$page_components->setWherePageId($id);
$page_components->deleteObjectCollection();

$custom_components = [];
foreach ($_POST as $component_name => $component) {
    foreach ($component as $tab_name => $fields) {
        foreach ($fields as $field_name => $field_data) {
            foreach ($field_data as $field_order => $field_value) {
                if (!$field_value) {
                    continue;
                }

                $page_component = new PageComponentCustomEntity();
                $page_component->setPageId($id);
                $page_component->setComponent($component_name);
                $page_component->setTab($tab_name);
                $page_component->setName($field_name);
                $page_component->setOrder($field_order);
                $page_component->setValue($field_value);
                $page_component->save();
            }
        }
    }
}

Structure::clearCache();

App::add('Custom Components on page "'. Structure::getPathById($id) .'" with id '. $id .' edited');
Messages::sendGreenAlert('Custom Components updated');

back();