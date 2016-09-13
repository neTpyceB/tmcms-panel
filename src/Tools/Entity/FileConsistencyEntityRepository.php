<?php

namespace TMCms\Admin\Tools\Entity;

use TMCms\Orm\EntityRepository;

class FileConsistencyEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_file_consistency';

    protected $table_structure = [
        'fields' => [
            'hash' => [
                'type' => 'char',
                'length' => '255'
            ],
            'file' => [
                'type' => 'varchar'
            ],
            'content' => [
                'type' => 'mediumtext'
            ],
            'ts' => [
                'type' => 'int',
                'unsigned' => true,
            ],
        ],
    ];
}