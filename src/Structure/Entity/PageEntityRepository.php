<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class PageEntityRepository
 * @package TMCms\Admin\Structure\Entity
 *
 * @method $this setWhereLocation(string $location)
 * @method $this setWherePid(int $pid)
 * @method $this setWhereActive(bool $flag)
 * @method $this setWhereInMenu(bool $flag)
 * @method $this setWhereMenuName(string $menu_name)
 * @method $this setWhereStringLabel(string $label)
 */
class PageEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_pages';
    protected $table_structure = [
        'fields' => [
            'template_id' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'pid' => [
                'type' => 'int',
                'unsigned' => true,
                'default' => 0,
            ],
            'location' => [
                'type' => 'varchar',
            ],
            'string_label' => [
                'type' => 'varchar',
            ],
            'title' => [
                'type' => 'varchar',
            ],
            'keywords' => [
                'type' => 'text',
            ],
            'description' => [
                'type' => 'text',
            ],
            'is_lng_page' => [
                'type' => 'bool',
            ],
            'in_menu' => [
                'type' => 'bool',
            ],
            'active' => [
                'type' => 'bool',
            ],
            'file_cache' => [
                'type' => 'bool',
            ],
            'transparent_get' => [
                'type' => 'bool',
            ],
            'go_level_down' => [
                'type' => 'bool',
            ],
            'redirect_url' => [
                'type' => 'varchar',
            ],
            'html_file' => [
                'type' => 'varchar',
            ],
            'order' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'lastmod_ts' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'menu_name' => [
                'type' => 'varchar',
                'default_value' => 'main',
            ],
        ],
        'indexes' => [
            'template_id' => [
                'type' => 'key',
            ],
            'pid' => [
                'type' => 'key',
            ],
        ]
    ];
}