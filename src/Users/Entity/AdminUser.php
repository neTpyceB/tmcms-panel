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
 * @method bool getActive()
 * @method string getAvatar()
 * @method string getEmail()
 * @method int getGroupId()
 * @method string getLogin()
 * @method string getLng()
 * @method string getName()
 * @method string getNotes()
 * @method string getSurname()
 *
 * @method $this setActive(bool $flag)
 * @method $this setGroupId(int $group_id)
 * @method $this setLogin(string $login)
 * @method $this setLng(string $language_code)
 * @method $this setPassword(string $password)
 */
class AdminUser extends Entity
{
    protected $db_table = 'cms_users';

    public function hashPassword()
    {
        $this->setPassword(Users::getInstance()->generateHash($this->getPassword()));
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->getField('password');
    }

    protected function beforeDelete()
    {
        // AdminUser log
        $res = new UserLogRepository();
        $res->setWhereUserId($this->getId());
        $res->deleteObjectCollection();

        // AdminUser messages - received
        $res = new UsersMessageEntityRepository();
        $res->setWhereToUserId($this->getId());
        $res->deleteObjectCollection();

        // AdminUser messages - sent
        $res = new UsersMessageEntityRepository();
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