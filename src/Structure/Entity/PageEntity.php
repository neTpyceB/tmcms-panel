<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\Entity;
use TMCms\Routing\Entity\PageComponentsDisabledEntityRepository;

/**
 * Class PageEntity
 * @package TMCms\Admin\Structure\Entity
 *
 * @method bool getActive()
 * @method int getPid()
 * @method string getLocation()
 *
 * @method $this setPid(int $pid)
 */
class PageEntity extends Entity
{
    protected $db_table = 'cms_pages';

    protected function beforeDelete()
    {
        $disabled = new PageComponentsDisabledEntityRepository();
        $disabled->setWherePageId($this->getId());
        $disabled->deleteObjectCollection();

        $history = new PageComponentHistoryRepository();
        $history->setWherePageId($this->getId());
        $history->deleteObjectCollection();

        $components = new PageComponentRepository();
        $components->setWherePageId($this->getId());
        $components->deleteObjectCollection();

        $clickmap = new PageClickmapRepository();
        $clickmap->setWherePageId($this->getId());
        $clickmap->deleteObjectCollection();

        parent::beforeDelete();
    }
}