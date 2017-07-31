<?php
declare(strict_types=1);

namespace TMCms\Admin\Users\Entity;

use TMCms\Admin\Users;
use TMCms\Orm\Entity;

/**
 * Class AdminUserGroup
 * @package TMCms\Admin\Users\Entity
 *
 * @method bool getCanSetPermissions()
 * @method bool getFullAccess()
 * @method string getTitle()
 * @method bool getUndeletable()
 * @method $this setCanSetPermissions(int $flag)
 * @method $this setDefault(int $flag)
 * @method $this setFilemanagerLimited(int $flag)
 * @method $this setFullAccess(int $flag)
 * @method $this setStructurePermissions(int $flag)
 */
class AdminUserGroup extends Entity
{
    public $is_super_admin = false; // Required for first site install
    protected $db_table = 'cms_users_groups';

    /**
     * @return $this
     */
    protected function beforeSave()
    {
        $can_set_permission = 0;

        // If user is super-admin with all privileges granted
        if ($this->is_super_admin) {
            $can_set_permission = 1;
        }

        // If current group is allowed to set permissions
        if ($this->getField('can_set_permissions') && Users::getInstance()->getGroupData('can_set_permissions')) {
            $can_set_permission = 1;
        }

        $this->setCanSetPermissions($can_set_permission);

        return $this;
    }
}