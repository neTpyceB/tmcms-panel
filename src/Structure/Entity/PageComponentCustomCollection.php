<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class PageComponentCustomCollection
 * @package TMCms\Admin\Structure\Entity
 *
 * @method $this setWhereComponent(string $component)
 * @method $this setWherePageId(int $page_id)
 */
class PageComponentCustomCollection extends EntityRepository
{
    protected $db_table = 'cms_pages_components_custom';
}