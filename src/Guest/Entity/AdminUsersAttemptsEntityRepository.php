<?php

namespace TMCms\Admin\Guest\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class AdminUsersAttemptsRepository
 * @package TMCms\Admin\Guest\Entity
 *
 * @method setWhereIp(string $ip)
 */
class AdminUsersAttemptsEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_users_attempts';
    protected $table_structure = [
        'fields' => [
            'ip' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'failed_attempts' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'last_attempt_ts' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'function_name' => [
                'type' => 'varchar',
            ],
        ],
    ];
}