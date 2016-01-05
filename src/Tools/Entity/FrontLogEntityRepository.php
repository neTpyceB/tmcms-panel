<?php

namespace TMCms\Log\Entity;

use TMCms\Orm\EntityRepository;

class FileConsistencyEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_file_consistency';

    protected $table_structure = [
        'fields' => [
            'hash' => [
                'type' => 'char'
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