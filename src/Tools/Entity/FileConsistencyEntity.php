<?php

namespace TMCms\Admin\Tools\Entity;

use TMCms\Orm\Entity;

/**
 * Class FileConsistencyEntity
 * @package TMCms\Admin\Tools\Entity
 *
 * @method string getHash()
 */
class FileConsistencyEntity extends Entity
{
    protected $db_table = 'cms_file_consistency';
}