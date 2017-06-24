<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\Entity;

/**
 * Class PageAliasEntity
 * @package TMCms\Admin\Structure\Entity
 *
 * @method string getName()
 *
 * @method $this setHref(string $link)
 * @method $this setIsLanding(int $flag)
 * @method $this setName(string $name)
 * @method $this setPageId(int $page_id)
 * @method $this setTs(int $ts)
 */
class PageAliasEntity extends Entity
{
    protected $db_table = 'cms_pages_aliases';
}