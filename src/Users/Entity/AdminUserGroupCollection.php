<?php

namespace TMCms\Admin\Users\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class AdminUserGroupCollection
 * @package TMCms\Admin\Users\Entity
 *
 * @method $this setDefault(bool $flag)
 * @method $this setWhereDefault(bool $flag)
 */
class AdminUserGroupCollection extends EntityRepository {
    protected $db_table = 'cms_users_groups';
}