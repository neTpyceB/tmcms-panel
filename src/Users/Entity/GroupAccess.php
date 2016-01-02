<?php

namespace TMCms\Admin\Users\Entity;

use TMCms\Orm\Entity;

/**
 * @method string getDo()
 * @method string getP()
 * @method $this setGroupId(int $id)
 * @method $this setDo(string $do)
 * @method $this setP(string $p)
 */
class GroupAccess extends Entity
{
    protected $db_table = 'cms_users_groups_access';
}