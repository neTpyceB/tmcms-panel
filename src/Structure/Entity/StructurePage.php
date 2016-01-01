<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\Entity;

/**
 * Class StructurePage
 * @package TMCms\Admin\Structure\Entity
 *
 * @method bool getActive()
 * @method string getLocation()
 */
class StructurePage extends Entity
{
    protected $db_table = 'cms_pages';
}