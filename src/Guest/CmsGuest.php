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
use TMCms\Templates\PageHead;
use TMCms\Templates\PageTail;
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

                if ($payload->created_at > strtotime('-5 minutes')) {
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

        PageHead::getInstance()
            ->addClassToBody('login')
            ->addCssUrl('cms/css/login-soft.css')
        ;

        $cms_cnf = $config->get('cms');
        if(array_key_exists('login', $cms_cnf) && array_key_exists('slides', $cms_cnf['login'])){
            $login_slides = $cms_cnf['login']['slides'];
            PageTail::getInstance()
                ->addJs('
                var login_slides = '.json_encode($login_slides).';
                ');
        }
        PageTail::getInstance()
            ->addJsUrl('cms/layout/scripts/login-soft.js')
            ->addJs('
                Login.init();
            ')
        ;

        // Logo image and link
        $logo= '';
        if (array_key_exists('login', $cms_cnf) && array_key_exists('logo', $cms_cnf['login'])) {
            $logo = $cms_cnf['login']['logo'];
        }
        if (!$logo && array_key_exists('logo', $cms_cnf)) {
            $logo = $cms_cnf['logo'];
        }
        $logo_link = DIR_CMS_URL;
        if (array_key_exists('logo_link', $cms_cnf)) {
            $logo_link = $cms_cnf['logo_link'];
        }

        // Registration form
        $registration_allowed = Settings::get('allow_registration');
        ?>

        <?php if ($logo): ?>
            <div class="logo">
                <a href="<?= $logo_link ?>" target="_blank">
                    <img src="<?= $logo ?>" alt="DEVP Web Development">
                </a>
            </div>
        <?php endif; ?>
        <div class="content">
            <form class="login-form" action="?p=<?= P ?>&do=_login" method="post">
                <?php if (isset($_GET['registered'])): ?>
                    <h3 class="form-title">User created. Contact admins to activate your account.</h3>
                    <script>
                        setTimeout(function() {
                            window.location = window.history.back();
                        }, 3000);
                    </script>';
                <?php endif; ?>

                <h3 class="form-title">Login to your account</h3>
                <div class="alert alert-danger display-hide">
                    <button class="close" data-close="alert"></button>
                    <span>Enter any username and password.</span>
                </div>
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Username</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input class="form-control placeholder-no-fix" type="text" autofocus placeholder="Username" name="login" <?= isset($_GET['login']) ? $_GET['login'] : '' ?>>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Password</label>
                    <div class="input-icon">
                        <i class="fa fa-lock"></i>
                        <input class="form-control placeholder-no-fix" type="password" placeholder="Password" name="password">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn blue">
                        Login <i class="m-icon-swapright m-icon-white"></i>
                    </button>
                </div>
                <input type="hidden" name="go" value="<?= SELF ?>">
                <div class="forget-password">
                    <h4>Forgot your password ?</h4>
                    <p>no worries, click <a href="javascript:;" id="forget-password">
                            here </a>
                        to reset your password.
                    </p>
                </div>
                <?php if ($registration_allowed): ?>
                    <div class="create-account">
                        <p>Don't have an account yet?&nbsp;
                            <a href="javascript:;" id="register-btn">Create an account </a>
                        </p>
                    </div>
                <?php endif; ?>
            </form>
            <form class="forget-form" action="?p=<?= P ?>&do=_reset_password" method="post">
                <h3>Forget Password ?</h3>
                <p>Enter your e-mail address below to reset your password.</p>
                <div class="form-group">
                    <div class="input-icon">
                        <i class="fa fa-envelope"></i>
                        <input class="form-control placeholder-no-fix" type="text" placeholder="Email" name="email">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" id="back-btn" class="btn">
                        <i class="m-icon-swapleft"></i> Back </button>
                    <button type="submit" class="btn blue pull-right">
                        Submit <i class="m-icon-swapright m-icon-white"></i>
                    </button>
                </div>
            </form>
            <?php if ($registration_allowed): ?>
                <form class="register-form" action="?p=<?= P ?>&do=_register" method="post">
                    <h3>Sign Up</h3>
                    <p>
                        Enter your personal details below:
                    </p>
                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Full Name</label>
                        <div class="input-icon">
                            <i class="fa fa-font"></i>
                            <input class="form-control placeholder-no-fix" type="text" placeholder="Full Name" name="name"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Email</label>
                        <div class="input-icon">
                            <i class="fa fa-envelope"></i>
                            <input class="form-control placeholder-no-fix" type="text" placeholder="Email" name="email"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Phone</label>
                        <div class="input-icon">
                            <i class="fa fa-envelope"></i>
                            <input class="form-control placeholder-no-fix" type="text" placeholder="Phone" name="phone"/>
                        </div>
                    </div>
                    <p>
                        Enter your account details below:
                    </p>
                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Username</label>
                        <div class="input-icon">
                            <i class="fa fa-user"></i>
                            <input class="form-control placeholder-no-fix" type="text" placeholder="Username" name="login">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Password</label>
                        <div class="input-icon">
                            <i class="fa fa-lock"></i>
                            <input class="form-control placeholder-no-fix" type="password" id="register_password" placeholder="Password" name="password"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">Re-type Your Password</label>
                        <div class="controls">
                            <div class="input-icon">
                                <i class="fa fa-check"></i>
                                <input class="form-control placeholder-no-fix" type="password" placeholder="Re-type Your Password" name="rpassword"/>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button id="register-back-btn" type="button" class="btn">
                            <i class="m-icon-swapleft"></i>Back
                        </button>
                        <button type="submit" id="register-submit-btn" class="btn blue pull-right">
                            Sign Up <i class="m-icon-swapright m-icon-white"></i>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        <?php if ($hide_license): ?>
            <!--
        <?php endif; ?>
        <div class="copyright">
            2007 - <?= Y ?> &copy; <?= CMS_NAME ?> | <a href="<?= CMS_SITE ?>" target="_blank"><?= CMS_SITE ?></a>
        </div>
        <?php if ($hide_license): ?>
            -->
        <?php endif;
    }

    public function _login()
    {
        if (!$_POST || !isset($_POST['login'], $_POST['password']) || trim($_POST['login']) == '') {
            back();
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

            // Check if no user exist in system
            Users::getInstance()->recreateDefaults();

            // Log attempt
            if (!$attempts_obj || !$attempts_obj->getId()) { // Check exists already
                $attempts_obj = new AdminUsersAttemptsEntity();
                $attempts_obj->setIp(IP_LONG);
            }
            $attempts_obj->setLastAttemptTs(NOW);
            $attempts_obj->setFailedAttempts($attempts_obj->getFailedAttempts() + 1);
            $attempts_obj->save();

            back();
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

        go(isset($_POST['go']) ? $_POST['go'] : '/cms/?p=home');
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

        go(SELF, ['registered' => 1]);
    }

    public function _reset_password() {
        die('Not allowed'); // TODO
    }
}