<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class TranslationRepository
 * @package TMCms\Admin\Structure\Entity
 */
class TranslationRepository extends EntityRepository
{
    protected $db_table = 'cms_translations';
    protected $table_structure = [
        'fields' => [

        ],
    ];
}