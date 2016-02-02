<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

class PageQuicklinkEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_pages_quicklinks';
    protected $table_structure = [
        'fields' => [
            'page_id' => [
                'type' => 'index',
            ],
            'name' => [
                'type' => 'varchar',
            ],
            'href' => [
                'type' => 'varchar',
            ],
            'searchword' => [
                'type' => 'bool',
            ],
        ],
    ];
}