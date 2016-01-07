<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

class PageRedirectHistoryEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_pages_redirect_history';
    protected $table_structure = [
        'fields' => [
            'page_id' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'component' => [
                'type' => 'varchar',
            ],
            'data' => [
                'type' => 'mediumtext',
            ],
            'ts' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'version' => [
                'type' => 'int',
                'unsigned' => true,
            ],
        ],
        'indexes' => [
            'page_id' => [
                'type' => 'key',
            ],
            'user_id' => [
                'type' => 'key',
            ],
        ],
    ];
}