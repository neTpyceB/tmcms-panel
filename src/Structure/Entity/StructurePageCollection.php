<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class StructurePageCollection
 * @package TMCms\Admin\Structure\Entity
 *
 * @method setWhereLocation(string $location)
 * @method setWherePid(int $pid)
 * @method setWhereActive(bool $flag)
 * @method setWhereInMenu(bool $flag)
 */
class StructurePageCollection extends EntityRepository
{
    protected $db_table = 'cms_pages';
}