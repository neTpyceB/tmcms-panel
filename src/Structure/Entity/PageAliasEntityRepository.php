<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class PageAliasEntityRepository
 * @package TMCms\Admin\Structure\Entity
 *
 * @method $this setWhereName(string $name)
 * @method $this setWhereIsLanding(int $flag)
 *
 * @method $this setHref(string $href)
 * @method $this setName(string $name)
 * @method $this setPageId(int $page_id)
 * @method $this setIsLanding(string $search_word)
 */
class PageAliasEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_pages_aliases';
    protected $table_structure = [
        'fields' => [
            'page_id'    => [
                'type' => 'index',
            ],
            'name'       => [
                'type' => 'varchar',
            ],
            'href'       => [
                'type' => 'varchar',
            ],
            'is_landing' => [
                'type' => 'bool',
            ],
        ],
    ];
}