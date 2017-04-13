<?php

namespace TMCms\Admin\Filemanager\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class FilePropertyEntityRepository
 * @package TMCms\Admin\Filemanager\Entity
 *
 * @method $this setWhereKey(string $key)
 * @method $this setWherePath(string $path)
 */
class FilePropertyEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_file_properties';
    protected $table_structure = [
        'fields' => [
            'path'  => [
                'type' => 'text',
            ],
            'key'   => [
                'type' => 'varchar',
            ],
            'value' => [
                'type' => 'text',
            ],
        ],
    ];
}