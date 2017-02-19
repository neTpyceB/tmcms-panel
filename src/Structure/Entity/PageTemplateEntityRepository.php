<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class PageTemplateEntityRepository
 * @package TMCms\Admin\Structure\Entity
 *
 * @method $this setWhereFile(string $file)
 */
class PageTemplateEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_pages_templates';
    protected $table_structure = [
        'fields' => [
            'file' => [
                'type' => 'varchar',
            ],
        ],
    ];
}