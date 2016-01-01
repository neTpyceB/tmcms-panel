<?php

namespace TMCms\Admin\Users\Entity;

use TMCms\Admin\Entity\UsersSessionEntityRepository;
use TMCms\Admin\Users;
use TMCms\Log\Entity\AppLogEntityRepository;
use TMCms\Orm\Entity;

/**
 * Class AdminUser
 * @package TMCms\Admin\Users\Entity
 *
 * @method string getLogin()
 * @method string getName()
 * @method string getSurname()
 * @method $this setActive(bool $flag)
 * @method $this setLogin(string $login)
 * @method $this setPassword(string $password)
 */
class AdminUser extends Entity
{
    protected $db_table = 'cms_users';

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->getField('password');
    }

    public function hashPassword()
    {
        $this->setPassword(Users::getInstance()->generateHash($this->getPassword()));
    }

    protected function beforeDelete()
    {
        // AdminUser log
        $res = new UserLogRepository();
        $res->setWhereUserId($this->getId());
        $res->deleteObjectCollection();

        // AdminUser messages - received
        $res = new UsersMessageRepository();
        $res->setWhereToUserId($this->getId());
        $res->deleteObjectCollection();

        // AdminUser messages - sent
        $res = new UsersMessageRepository();
        $res->setWhereFromUserId($this->getId());
        $res->deleteObjectCollection();

        // AdminUser sessions
        $res = new UsersSessionEntityRepository();
        $res->setWhereUserId($this->getId());
        $res->deleteObjectCollection();

        // App log
        $log = new AppLogEntityRepository();
        $log->setWhereUserId($this->getId());
        $log->deleteObjectCollection();
    }
}