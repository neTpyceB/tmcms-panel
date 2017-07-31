<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Admin\Users\Entity\AdminUser;
use TMCms\Admin\Users\Entity\UsersMessageEntity;
use TMCms\Strings\Converter;

ob_start();

$messages = Messages::receiveMessages((int)$_GET['user_id']);

foreach ($messages as $message): /** @var UsersMessageEntity $message */
    $user_from = new AdminUser($message->getFromUserId());

    // My message
    if ($user_from->getId() == USER_ID):
        ?>
        <div class="chatbox-user">
            <span class="chat-avatar pull-left">
                <?= $user_from->getName() ?>
            </span>
            <div class="message">
                <div class="panel">
                    <div class="panel-body">
                        <p><?= htmlspecialchars($message->getMessage(), ENT_QUOTES) ?></p>
                    </div>
                </div>
                <small class="chat-time">
                    <i class="ti-time mr5"></i>
                    <b><?= Converter::getTimeFromEventAgo($message->getTs()) ?></b>
                </small>
            </div>
        </div>
    <?php else: // Someone to me ?>
        <div class="chatbox-user right">
            <span class="chat-avatar pull-right">
                <?= $user_from->getName() ?>
            </span>
            <div class="message">
                <div class="panel">
                    <div class="panel-body">
                        <p><?= htmlspecialchars($message->getMessage(), ENT_QUOTES) ?></p>
                    </div>
                </div>
                <small class="chat-time">
                    <i class="ti-time mr5"></i>
                    <b><?= Converter::getTimeFromEventAgo($message->getTs()) ?></b>
                </small>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach;

echo ob_get_clean();
die;