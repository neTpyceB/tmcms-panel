<?php
declare(strict_types=1);

namespace TMCms\Admin\Users\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class AdminUserCollection
 * @package TMCms\Admin\Users\Entity
 *
 * @method $this setWhereActive(int $flag)
 * @method $this setWhereGroupId(int $group_id)
 * @method $this setWhereLogin(string $login)
 * @method $this setWherePassword(string $password)
 */
class AdminUserRepository extends EntityRepository
{
    protected $db_table = 'cms_users';
    protected $table_structure = [
        'fields' => [
            'group_id' => [
                'type' => 'index',
            ],
            'login' => [
                'type' => 'varchar',
            ],
            'name' => [
                'type' => 'varchar',
            ],
            'surname' => [
                'type' => 'varchar',
            ],
            'phone' => [
                'type' => 'varchar',
            ],
            'email' => [
                'type' => 'varchar',
            ],
            'avatar' => [
                'type' => 'varchar',
            ],
            'comments' => [
                'type' => 'text',
            ],
            'notes' => [
                'type' => 'text',
            ],
            'lng' => [
                'type' => 'char',
                'length' => 2,
            ],
            'password' => [
                'type' => 'char',
                'length' => 128,
            ],
            'active' => [
                'type' => 'bool',
            ],
        ],
    ];
}
