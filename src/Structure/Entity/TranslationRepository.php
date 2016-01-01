<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

class TranslationRepository extends EntityRepository
{
    protected $db_table = 'cms_translations';
}