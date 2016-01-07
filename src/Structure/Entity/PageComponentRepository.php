<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class PageComponentRepository
 * @package TMCms\Admin\Structure\Entity
 *
 * @method $this setWherePageId(int $page_id)
 */
class PageComponentRepository extends EntityRepository
{
    protected $db_table = 'cms_pages_components';
    protected $table_structure = [
        'fields' => [
            'page_id' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'component' => [
                'type' => 'varchar',
            ],
            'data' => [
                'type' => 'mediumtext',
            ],
        ],
        'indexes' => [
            'page_id' => [
                'type' => 'key',
            ],
        ],
    ];
}