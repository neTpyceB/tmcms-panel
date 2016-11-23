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
            'old_full_url' => [
                'type' => 'varchar',
            ],
            'new_full_url' => [
                'type' => 'varchar',
            ],
            'last' => [
                'type' => 'bool',
            ],
        ],
        'indexes' => [
            'page_id' => [
                'type' => 'key',
            ],
        ],
    ];
}