<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class PageComponentRepository
 * @package TMCms\Admin\Structure\Entity
 *
 * @method $this setWherePageId(int $page_id)
 */
class PageComponentRepository extends EntityRepository
{
    protected $db_table = 'cms_pages_components';
}