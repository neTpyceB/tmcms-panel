<?php

namespace TMCms\Admin\Users\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class UserLogRepository
 * @package TMCms\Admin\Users\Entity
 *
 * @method $this setWhereUserId(int $id)
 */
class UserLogRepository extends EntityRepository
{
    protected $db_table = 'cms_users_log';
}