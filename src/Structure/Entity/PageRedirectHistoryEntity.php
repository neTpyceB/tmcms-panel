<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\Entity;

/**
 * Class PageRedirectHistoryEntity
 * @package TMCms\Admin\Structure\Entity
 *
 * @method string getNewFullUrl()
 */
class PageRedirectHistoryEntity extends Entity
{
    protected $db_table = 'cms_pages_redirect_history';
}