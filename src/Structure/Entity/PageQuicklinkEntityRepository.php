<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class PageQuicklinkEntityRepository
 * @package TMCms\Admin\Structure\Entity
 *
 * @method $this setWhereName(string $name)
 * @method $this setWhereSearchword(int $flag)
 */
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