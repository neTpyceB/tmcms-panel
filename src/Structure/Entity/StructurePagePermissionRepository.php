<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * @method setWhereGroupId(int $group_id)
 */
class StructurePagePermissionRepository extends EntityRepository
{
    protected $db_table = 'cms_pages_permissions';
    protected $table_structure = [
        'fields' => [
            'page_id' => [
                'type' => 'index',
            ],
            'group_id' => [
                'type' => 'index',
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
    ];
}