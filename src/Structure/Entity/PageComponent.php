<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\Entity;

/**
 * Class PageComponent
 * @package TMCms\Admin\Structure\Entity
 *
 * @method string getComponent()
 * @method string getData()
 * @method int getPageId()
 */
class PageComponent extends Entity
{
    protected $db_table = 'cms_pages_components';
}