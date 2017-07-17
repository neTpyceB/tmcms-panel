<?php
declare(strict_types=1);

namespace TMCms\Admin\Structure\Entity;

defined('INC') or exit;

use TMCms\Orm\Entity;

/**
 * Class PageComponentCustomEntity
 * @package TMCms\Admin\Structure\Entity
 *
 * @method int getOrder()
 *
 * @method $this setComponent(string $component)
 * @method $this setName(string $name)
 * @method $this setOrder(int $order)
 * @method $this setPageId(int $id)
 * @method $this setTab(string $tab)
 * @method $this setValue(string $value)
 */
class PageComponentCustomEntity extends Entity
{
    protected $db_table = 'cms_pages_components_custom';
}