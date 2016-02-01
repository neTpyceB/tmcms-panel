<?php

namespace TMCms\Admin\Users\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class AdminUserGroupCollection
 * @package TMCms\Admin\Users\Entity
 *
 * @method $this setDefault(bool $flag)
 * @method $this setWhereDefault(bool $flag)
 */
class AdminUserGroupRepository extends EntityRepository {
    protected $db_table = 'cms_users_groups';
    protected $table_structure = [
        'fields' => [
            'title' => [
                'type' => 'varchar',
            ],
            'undeletable' => [
                'type' => 'bool',
            ],
            'can_set_permissions' => [
                'type' => 'bool',
            ],
            'full_access' => [
                'type' => 'bool',
            ],
            'structure_permissions' => [
                'type' => 'bool',
            ],
            'filemanager_limited' => [
                'type' => 'bool',
            ],
        ],
    ];
}