<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\Entity;

class PageQuicklinkEntity extends Entity
{
    protected $db_table = 'cms_pages_quicklinks';

    protected function beforeSave()
    {
        $this->setTs(NOW);

        parent::beforeSave();
    }
}