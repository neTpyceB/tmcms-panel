<?php

namespace TMCms\Admin\Users\Entity;

use TMCms\Orm\Entity;

/**
 * @method string getP()
 * @method string getDo()
 */
class GroupAccess extends Entity
{
    protected $db_table = 'cms_users_groups_access';
}