<?php

namespace TMCms\Admin\Structure\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class TranslationRepository
 * @package TMCms\Admin\Structure\Entity
 *
 * @method $this setEntity(string $entity)
 * @method $this setEntityId(int $entity_id)
 */
class TranslationRepository extends EntityRepository
{
    protected $db_table = 'cms_translations';
    protected $table_structure = [
        'fields' => [
            'entity' => [
                'type' => 'varchar',
            ],
            'entity_id' => [
                'type' => 'ts',
            ],
        ],
    ];
}