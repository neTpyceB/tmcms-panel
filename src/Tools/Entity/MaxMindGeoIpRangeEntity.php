<?php
declare(strict_types=1);

namespace TMCms\Admin\Tools\Entity;

use TMCms\Orm\Entity;

/**
 * Class MaxMindGeoIpRangeEntity
 * @package TMCms\Admin\Tools\Entity
 *
 * @method string getCode()
 */
class MaxMindGeoIpRangeEntity extends Entity
{
    protected $db_table = 'cms_maxmind_geoip_r';
}