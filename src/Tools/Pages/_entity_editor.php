<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Orm\Entity;
use TMCms\Strings\Converter;

$entity = new Entity();
$entity->setDbTableName($_POST['table_name']);
$entity->setId($_POST['entity_id']);

unset($_POST['table_name'], $_POST['entity_id']);

foreach ($_POST as $field => $value) {
    // For translations
    if (is_array($value)) {
        $entity->addTranslationFieldForAutoSelects($field);
    }

    $method = 'set'. Converter::toCamelCase($field);
    $entity->$method($value);
}

$entity->save();

$message = 'Update Entity by using Entity Editor, from table  "' . $entity->getDbTableName() . '" with ID ' . $entity->getId();

Messages::sendGreenAlert($message);
App::add($message);

back();

back();
