<?php

namespace TMCms\Admin\Users\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class UsersMessageRepository
 * @package TMCms\Admin\Users\Entity
 *
 * @method $this setSeen(int $flag)
 * @method $this setWhereFromUserId(int $user_id)
 * @method $this setWhereMessage(string $message)
 * @method $this setWhereSeen(int $flag)
 * @method $this setWhereToUserId(int $user_id)
 */
class UsersMessageEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_users_messages';

    protected $table_structure = [
        'fields' => [
            'from_user_id' => [
                'type' => 'index',
            ],
            'to_user_id' => [
                'type' => 'index',
            ],
            'message' => [
                'type' => 'text',
            ],
            'seen' => [
                'type' => 'bool',
            ],
            'ts' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'notify' => [
                'type' => 'bool',
            ],
        ],
    ];

    public function setWhereOld()
    {
        $this->addWhereFieldIsLower('ts', NOW - 604800); // One week
    }
}