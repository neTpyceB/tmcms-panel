<?php

namespace TMCms\Admin\Guest\Entity;

use neTpyceB\TMCms\Orm\Entity;

/**
 * Class AdminUsersAttempts
 * @package neTpyceB\TMCms\Admin\Guest\Entity
 *
 * @method int getFailedAttempts()
 * @method setFailedAttempts(int $ts)
 * @method setIp(string $ip)
 * @method setLastAttemptTs(int $ts)
 */
class AdminUsersAttemptsEntity extends Entity
{
    protected $db_table = 'cms_users_attempts';
}