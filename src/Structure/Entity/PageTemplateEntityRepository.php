<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

class PageTemplateEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_pages_templates';
}