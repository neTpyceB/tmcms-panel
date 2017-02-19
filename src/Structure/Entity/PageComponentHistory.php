<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\Entity;

/**
 * Class PageComponentHistory
 * @package TMCms\Admin\Structure\Entity
 *
 * @method string getData()
 * @method int getVersion()
 */
class PageComponentHistory extends Entity
{
    protected $db_table = 'cms_pages_components_history';
}