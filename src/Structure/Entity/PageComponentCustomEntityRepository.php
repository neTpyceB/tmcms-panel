<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class PageComponentCustomCollection
 * @package TMCms\Admin\Structure\Entity
 *
 * @method $this setWhereComponent(string $component)
 * @method $this setWherePageId(int $page_id)
 */
class PageComponentCustomEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_pages_components_custom';
    protected $table_structure = [
        'fields' => [
            'page_id' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'component' => [
                'type' => 'varchar',
            ],
            'tab' => [
                'type' => 'varchar',
            ],
            'name' => [
                'type' => 'varchar',
            ],
            'value' => [
                'type' => 'text',
            ],
            'order' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'active' => [
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