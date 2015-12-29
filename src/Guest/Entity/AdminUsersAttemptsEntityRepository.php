<?php

namespace TMCms\Admin\Guest\Entity;

use neTpyceB\TMCms\Orm\EntityRepository;

/**
 * Class AdminUsersAttemptsRepository
 * @package neTpyceB\TMCms\Admin\Guest\Entity
 *
 * @method setWhereIp(string $ip)
 */
class AdminUsersAttemptsEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_users_attempts';
}