<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

class PageRedirectHistoryEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_pages_redirect_history';
}