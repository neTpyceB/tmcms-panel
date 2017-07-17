<?php
declare(strict_types=1);

namespace TMCms\Admin\Tools\Entity;

use TMCms\Orm\EntityRepository;

class MaxMindGeoIpCountryEntityRepository extends EntityRepository
{
    protected $db_table = 'cms_maxmind_geoip_c';
    protected $table_structure = [
        'fields'  => [
            'code'    => [
                'type'   => 'char',
                'length' => 2,
            ],
            'country' => [
                'type'   => 'char',
                'length' => 38,
            ],
        ],
        'indexes' => [
            'code' => [
                'type' => 'key',
            ],
        ],
    ];
}