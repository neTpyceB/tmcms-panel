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
}