<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class PageComponentHistoryRepository
 * @package TMCms\Admin\Structure\Entity
 *
 * @method setWhereVersion(int $version)
 */
class PageComponentHistoryRepository extends EntityRepository
{
    protected $db_table = 'cms_pages_components_history';
}