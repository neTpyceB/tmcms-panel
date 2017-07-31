<?php
declare(strict_types=1);

use TMCms\Admin\Messages;

ob_start();
Messages::sendMessage($_POST['message'], $_POST['to_user_id'], USER_ID);
ob_get_clean();
die('1');