<?php

namespace TMCms\Admin\Users\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class UsersMessageRepository
 * @package TMCms\Admin\Users\Entity
 *
 * @method $this setWhereFromUserId(int $user_id)
 * @method $this setWhereToUserId(int $user_id)
 */
class UsersMessageRepository extends EntityRepository
{
    public function setWhereOld()
    {
        $this->addWhereFieldIsLower('ts', NOW - 604800); // One week
    }
}