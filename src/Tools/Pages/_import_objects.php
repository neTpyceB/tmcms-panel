<?php
declare(strict_types=1);

use TMCms\Orm\Entity;

$file = $_FILES['file'];

if (is_uploaded_file($file['tmp_name'])) {
    $res = unserialize(file_get_contents($file['tmp_name']));

    if (isset($res['class'])) {
        require_once DIR_BASE . $res['class'];

        $res['objects'] = unserialize($res['objects']);

        // Create objects
        if (isset($res['objects'])) {
            foreach ($res['objects'] as $object) {
                /** @var Entity $object */
                // Clear ID - let it create new object
                $object->setId('');
                // Trigger all fields update
                $object->loadDataFromArray($object->getAsArray());

                $object->save();
            }
        }
    }
}

echo 'Done';