<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\Entity;

/**
 * Class PageClickmap
 * @package TMCms\Admin\Structure\Entity
 *
 * @method $this setTs(int $ts)
 */
class PageClickmap extends Entity
{
    protected $db_table = 'cms_pages_clickmap';

    protected function beforeSave()
    {
        $this->setTs(NOW);

        parent::beforeSave();
    }
}