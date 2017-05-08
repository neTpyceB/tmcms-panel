<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class PageQuicklinkEntityRepository
 * @package TMCms\Admin\Structure\Entity
 *
 * @method $this setWhereName(string $name)
 * @method $this setWhereSearchword(int $flag)
 *
 * @method $this setHref(string $href)
 * @method $this setName(string $name)
 * @method $this setPageId(int $page_id)
 * @method $this setSearchword(string $search_word)
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