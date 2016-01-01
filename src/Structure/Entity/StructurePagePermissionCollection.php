<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * @method setWhereGroupId(int $group_id)
 */
class StructurePagePermissionCollection extends EntityRepository
{
    protected $db_table = 'cms_pages_permissions';
}