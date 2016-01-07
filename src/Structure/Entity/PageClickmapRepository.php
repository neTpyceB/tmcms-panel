<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class PageClickmapRepository
 * @package TMCms\Admin\Structure\Entity
 *
 * @method $this setWhereIpLong(int $ip_long)
 * @method $this setWherePageId(int $page_id)
 */
class PageClickmapRepository extends EntityRepository
{
    protected $db_table = 'cms_pages_clickmap';
    protected $table_structure = [
        'fields' => [
            'page_id' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'x' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'y' => [
                'type' => 'int',
                'unsigned' => true,
            ],
        ],
        'indexes' => [
            'page_id' => [
                'type' => 'key',
            ],
        ],
    ];
}