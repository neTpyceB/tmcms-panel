<?php

namespace TMCms\Admin\Users\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class UserLogRepository
 * @package TMCms\Admin\Users\Entity
 *
 * @method $this setWhereDo(string $p_do)
 * @method $this setWhereP(string $p)
 * @method $this setWhereUserId(int $id)
 */
class UserLogRepository extends EntityRepository
{
    protected $db_table = 'cms_users_log';
    protected $table_structure = [
        'fields' => [
            'user_id' => [
                'type' => 'index',
            ],
            'sid' => [
                'type' => 'char',
                'length' => 32,
            ],
            'agent' => [
                'type' => 'varchar',
            ],
            'p' => [
                'type' => 'varchar',
            ],
            'do' => [
                'type' => 'varchar',
            ],
            'request_uri' => [
                'type' => 'text',
            ],
            'referer' => [
                'type' => 'text',
            ],
            'post' => [
                'type' => 'mediumtext',
            ],
            'ts' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'ip_long' => [
                'type' => 'int',
                'unsigned' => true,
            ],
        ],
    ];
}