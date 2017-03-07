<?php

namespace TMCms\Admin\Filemanager\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class FileMetaEntityRepository
 * @package TMCms\Admin\Filemanager\Entity
 *
 * @method $this setWherePath(string $path)
 */
class FileMetaEntityRepository extends EntityRepository
{
    protected $table_structure = [
        'fields' => [
            'path' => [
                'type' => 'text',
            ],
            'data' => [
                'type' => 'text',
            ],
        ],
    ];
}