<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class PageComponentHistoryRepository
 * @package TMCms\Admin\Structure\Entity
 *
 * @method $this setWherePageId(int $page_id)
 * @method $this setWhereVersion(int $version)
 */
class PageComponentHistoryRepository extends EntityRepository
{
    protected $db_table = 'cms_pages_components_history';
}