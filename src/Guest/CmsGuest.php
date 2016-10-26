<?php

namespace TMCms\Admin\Guest;

use Exception;
use TMCms\Admin\Guest\Entity\AdminUsersAttemptsEntity;
use TMCms\Admin\Guest\Entity\AdminUsersAttemptsEntityRepository;
use TMCms\Admin\Users;
use TMCms\Admin\Users\Entity\AdminUser;
use TMCms\Admin\Users\Entity\AdminUserRepository;
use TMCms\Admin\Users\Entity\AdminUserGroupRepository;
use TMCms\Config\Configuration;
use TMCms\Config\Settings;
use TMCms\Log\App;
use TMCms\Strings\JWT;
use TMCms\Strings\Verify;
use TMCms\Traits\singletonInstanceTrait;

defined('INC') or exit;

class CmsGuest
{
    use singletonInstanceTrait;

    const FIRST_BAN_TIME = 60;
    const FIRST_FAILED_ATTEMPTS = 5;
    const MAX_BAN_TIME = 3600;
    const MAX_FAILED_ATTEMPTS = 10;

    public function _default()
    {
        // If only unique access allowed
        if (Settings::getInstance()->get('unique_admin_address')) {
            // No correct key provided?
            if (!isset($_GET['admin_key']) || $_GET['admin_key'] != Configuration::getInstance()->get('cms')['unique_key']) {
                back();
            }
        }

        // Authorize user by provided token (used by our mobile application)
        if (isset($_GET['token'])) {
            try {
                $payload = JWT::decode($_GET['token'], date('Y-m-d', NOW), true);

                if ($payload->createdAt > strtotime('-5 minutes')) {
                    $user_collection = new AdminUserRepository();
                    $user_collection->setWhereLogin($payload->login);
                    $user_collection->setWherePassword($payload->password);
                    $user_collection->setWhereActive(1);

                    /** @var AdminUser $user */
                    $user = $user_collection->getFirstObjectFromCollection();

                    if ($user) {
                        $this->initLogInProcess($user);
                    }
                }
            } catch (Exception $exception) {
                // Do nothing, I guess...
            }
        }

        // Redirect if user is already logged in
        if (Users::getInstance()->isLogged()) {
            go('/cms/?p=home');
        }

        $config = Configuration::getInstance();

        $expose = $config->get('options');
        $hide_license = $expose && isset($expose['hide_license']) && $expose['hide_license'];

        ?>
        <div class="overlay bg-primary"></div>

        <div class="center-wrapper">
            <div class="center-content">
                <div class="row">
                    <div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
                        <section class="panel bg-white no-b">
                            <?php if (Settings::get('allow_registration')): ?>
                                <ul class="switcher-dash-action">
                                    <li class="active"><a href="" onclick="return false" class="selected">Sign in</a>
                                    </li>
                                    <li><a href="?p=<?= P ?>&do=register" class="">New account</a></li>
                                </ul>
                            <?php endif; ?>
                            <div class="p15">
                                <form role="form" action="?p=<?= P ?>&do=_login" method="post">
                                    <div>
                                        <input type="text" class="form-control input-lg mb25" value="<?= isset($_GET['login']) ? $_GET['login'] : '' ?>" placeholder="Username" name="login" autofocus>
                                    </div>
                                    <div>
                                        <input type="password" class="form-control input-lg mb25" placeholder="Password" name="password">
                                    </div>
                                    <input type="hidden" name="go" value="<?= SELF ?>">
                                    <button class="btn btn-primary btn-lg btn-block" style="background-color: #0f0f0f" type="submit">Sign in</button>
                                </form>
                            </div>
                        </section>
                        <?php if ($hide_license): ?>
                            <!--
                        <?php endif; ?>
                            <p class="text-center">
                                <span>This site uses <?= CMS_NAME ?> (TMCms v. <?= CMS_VERSION ?>)
                                    <br>
                                    <a target="_blank" href="<?= CMS_SITE ?>"><?= CMS_SITE ?></a>
                                </span>
                            </p>
                        <?php if ($hide_license): ?>
                            -->
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }

    public function _login()
    {
        // Clear installation
        if (isset($_GET['reset_system_users'], $_GET['key']) && $_GET['key'] == Configuration::getInstance()->get('cms')['unique_key']) {
            $user_collection = new AdminUserRepository();
            $user_collection->deleteObjectCollection();
            back();
        }

        if (!$_POST || !isset($_POST['login'], $_POST['password']) || trim($_POST['login']) == '') {
            sleep(5);
            go('/');
        }

        //Check ban and log-in attempts
        $attempts_repo = new AdminUsersAttemptsEntityRepository();
        $attempts_repo->setWhereIp(IP_LONG);
        $attempts_obj = $attempts_repo->getFirstObjectFromCollection();

        if ($attempts_obj) {
            $attempts = $attempts_obj->getAsArray();
            // Got info, check times
            if ($attempts && $attempts['failed_attempts']) {
                if ($attempts['failed_attempts'] > self::MAX_FAILED_ATTEMPTS && $attempts['last_attempt_ts'] + self::MAX_BAN_TIME > NOW) {
                    die('IP banned, wait till ' . date('H:i:s', $attempts['last_attempt_ts'] + self::MAX_BAN_TIME));
                } elseif ($attempts['failed_attempts'] > self::FIRST_FAILED_ATTEMPTS && $attempts['last_attempt_ts'] + self::FIRST_BAN_TIME > NOW) {
                    die('IP banned, wait till ' . date('H:i:s', $attempts['last_attempt_ts'] + self::FIRST_BAN_TIME));
                }
            }
        }

        // Get user info
        $user_collection = new AdminUserRepository();
        $user_collection->setWhereLogin($_POST['login']);
        $user_collection->setWhereActive(1);
        $user_collection->setWherePassword(Users::getInstance()->generateHash($_POST['password']));

        /** @var AdminUser $user */
        $user = $user_collection->getFirstObjectFromCollection();

        if ($user) {
            // Removing user's bans
            $attempts_repo->deleteObjectCollection();

            // Auth in
            $this->initLogInProcess($user);
        } else {

            // Check if first user is in system
            Users::getInstance()->recreateDefaults();

            // Log attempt
            if (!$attempts_obj || !$attempts_obj->getId()) { // Check exists already
                $attempts_obj = new AdminUsersAttemptsEntity();
                $attempts_obj->setIp(IP_LONG);
            }
            $attempts_obj->setLastAttemptTs(NOW);
            $attempts_obj->setFailedAttempts($attempts_obj->getFailedAttempts() + 1);
            $attempts_obj->save();

            sleep(5);
            go('/');
        }
    }

    /**
     * @param AdminUser $user
     */
    private function initLogInProcess($user) {
        $user->loadDataFromDB();

        // Set constants and session
        Users::getInstance()->deleteSession($user->getId());

        Users::getInstance()->setUserLoggedIn($user);

        go(isset($_POST['go']) ? $_POST['go'] : '/home/');
    }

    public function register()
    {
        if (Users::getInstance()->isLogged() || !Settings::get('allow_registration')) {
            go('/cms/?p=home');
        }


        $key = Configuration::getInstance()->get('cms')['unique_key'];

        ?>
        <div class="overlay bg-primary"></div>
        <div class="center-wrapper">
            <div class="center-content">
                <div class="row">
                    <div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
                        <section class="panel bg-white no-b">

                            <?php
                            if (isset($_GET['registered'])) {
                                echo 'New user registered. Administrators will contact you.
                                <script>
                                    setTimeout(function() {
                                        window.location = ' . DIR_CMS_URL . ';
                                    }, 5000);
                                </script>';
                                return;
                            } ?>

                            <ul class="switcher-dash-action">
                                <li><a href="?p=<?= P ?>&do=login" class="selected">Sign in</a></li>
                                <li class="active"><a href="" onclick="return false" class="">New account</a></li>
                            </ul>
                            <div class="p15">
                                <form role="form" action="?p=<?= P ?>&do=_register" method="post">
                                    <input type="text" class="form-control input-lg mb25"
                                           placeholder="Choose a username" autofocus name="login">
                                    <input type="text" class="form-control input-lg mb25" placeholder="Email address"
                                           name="email">
                                    <input type="password" class="form-control input-lg mb25" placeholder="Password"
                                           name="password">

                                    <button class="btn btn-primary btn-lg btn-block" type="submit">Sign up</button>
                                </form>
                            </div>
                        </section>
                        <p class="text-center">
                            <span><?= $key ? 'Licensed' : 'Unregistered' ?> proprietary software from <?= CMS_OWNER_COMPANY ?>
                                <br><br>
                                <?= CMS_NAME . ' (TMCms v. ' . CMS_VERSION . ')' ?> <a target="_blank" href="<?= CMS_SITE ?>"><?= CMS_SITE ?></a></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function _register()
    {
        if (!$_POST || !isset($_POST['login'], $_POST['email'], $_POST['password']) || trim($_POST['login']) == '') {
            sleep(5);
            go('/');
        }

        // Check user exists
        $user_collection = new AdminUserRepository();
        $user_collection->setWhereLogin($_POST['login']);

        if ($user_collection->hasAnyObjectInCollection()) {
            error('User with this login already exists');
        }

        // Create new user
        $default_group_id = 1;

        $group_collection = new AdminUserGroupRepository();
        $group_collection->setWhereDefault(true);

        /** @var AdminUser $user */
        $group = $group_collection->getFirstObjectFromCollection();
        if ($group) {
            $default_group_id = $group->getId();
        }

        $user = new AdminUser;
        $user->loadDataFromArray($_POST);

        if ($user->getEmail() && !Verify::email($user->getEmail())) {
            error('Wrong email');
        }

        $user->setGroupId($default_group_id);
        $user->setPassword($_POST['password']);
        $user->save();

        // TODO send email to new user with confirmation link
        // TODO make "restore password"

        go('?p=' . P . '&do=register&registered');
    }
}