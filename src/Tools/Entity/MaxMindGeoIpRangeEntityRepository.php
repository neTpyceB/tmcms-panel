<?php
declare(strict_types=1);

namespace TMCms\Admin\Tools\Entity;

use TMCms\Orm\EntityRepository;

class MaxMindGeoIpRangeEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_maxmind_geoip_r';
    protected $table_structure = [
        'fields'  => [
            'country_code' => [
                'type'   => 'char',
                'length' => 2,
            ],
            'start'        => [
                'type' => 'ts',
            ],
            'end'          => [
                'type' => 'ts',
            ],
        ],
        'indexes' => [
            'country_code' => [
                'type' => 'key',
            ],
        ],
    ];
}