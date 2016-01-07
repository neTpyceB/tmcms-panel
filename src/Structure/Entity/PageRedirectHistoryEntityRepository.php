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
            'group_id' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'edit' => [
                'type' => 'bool',
            ],
            'properties' => [
                'type' => 'bool',
            ],
            'active' => [
                'type' => 'bool',
            ],
            'delete' => [
                'type' => 'bool',
            ],
        ],
        'indexes' => [
            'page_id' => [
                'type' => 'key',
            ],
            'group_id' => [
                'type' => 'key',
            ],
        ],
    ];
}