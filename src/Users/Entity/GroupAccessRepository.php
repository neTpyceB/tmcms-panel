<?php

namespace TMCms\Admin\Users\Entity;

use TMCms\Orm\EntityRepository;

/**
 * @method $this setWhereGroupId(int $group_id)
 */
class GroupAccessRepository extends EntityRepository
{
    protected $db_table = 'cms_users_groups_access';
}