<?php

namespace TMCms\Admin\Users\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class AdminUserCollection
 * @package TMCms\Admin\Users\Entity
 *
 * @method $this setWhereActive(bool $flag)
 * @method $this setWhereGroupId(int $group_id)
 * @method $this setWhereLogin(string $login)
 * @method $this setWherePassword(string $password)
 */
class AdminUserCollection extends EntityRepository
{
    protected $db_table = 'cms_users';
}