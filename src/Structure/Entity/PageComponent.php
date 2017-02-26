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
 *
 * @method $this setComponent(string $component)
 * @method $this setData(string $data_content)
 * @method $this setPageId(int $page_id)
 */
class PageComponent extends Entity
{
    protected $db_table = 'cms_pages_components';
}