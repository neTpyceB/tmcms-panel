<?php

namespace TMCms\Admin\Users\Entity;

use TMCms\Orm\Entity;

/**
 * @method int getUserId()
 *
 * @method $this setAgent(string $agent)
 * @method $this setDo(string $do)
 * @method $this setIpLong(int $ip)
 * @method $this setP(string $p)
 * @method $this setReferer(string $ref)
 * @method $this setRequestUri(string $uri)
 * @method $this setPost(string $post)
 * @method $this setSid(int $sid)
 * @method $this setTs(int $ts)
 * @method $this setUserId(int $id)
 */
class UserLog extends Entity
{
    protected $db_table = 'cms_users_log';

    protected function beforeCreate()
    {
        $this->setSid($_SESSION['admin_sid']);
        $this->setTs(NOW);
        $this->setIpLong(IP_LONG);
        $this->setAgent(USER_AGENT);
        $this->setRequestUri(SELF);
        $this->setReferer(REF);
        $this->setReferer(REF);
        $this->setUserId(USER_ID);
        $this->setP(P);
        $this->setDo(P_DO);
        $this->setPost($_POST ? sql_prepare(serialize($_POST)) : '');
    }
}