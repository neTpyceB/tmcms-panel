<?php

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
 * @method $this setCanSetPermissions(bool $flag)
 * @method $this setDefault(bool $flag)
 * @method $this setFilemanagerLimited(bool $flag)
 * @method $this setFullAccess(bool $flag)
 * @method $this setStructurePermissions(bool $flag)
 */
class AdminUserGroup extends Entity
{
    public $is_superadmin = false; // Required for first site install
    protected $db_table = 'cms_users_groups';

    /**
     * @return $this
     */
    protected function beforeSave()
    {
        $can_set_permission = false;

        // If user is super-admin with all privileges granted
        if ($this->is_superadmin) {
            $can_set_permission = true;
        }

        // If current group is allowed to set permissions
        if ($this->getField('can_set_permissions') && Users::getInstance()->getGroupData('can_set_permissions')) {
            $can_set_permission = true;
        }

        $this->setCanSetPermissions($can_set_permission);

        return $this;
    }
}