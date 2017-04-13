<?php

namespace TMCms\Admin\Filemanager\Entity;

use TMCms\Orm\Entity;

/**
 * Class FilePropertyEntity
 * @package TMCms\Admin\Filemanager\Entity
 *
 * @method string getKey()
 * @method string getPath()
 * @method string getValue()
 *
 * @method setKey(string $key)
 * @method setPath(string $path)
 * @method setValue(string $value)
 */
class FilePropertyEntity extends Entity
{
    protected $db_table = 'cms_file_properties';
}