<?php
declare(strict_types=1);

if (!isset($_GET['user_id'])) {
    return;
}
$from_user_id = (int)$_GET['user_id'];
?>

    <form class="form-input clearfix mt10 mb10" id="send-message-form" action="?p=users&do=_ajax_send_message" method="post">
        <div class="input-group">
            <input type="text" id="chat_message" class="form-control input-sm" placeholder="Say Something...">
            <span class="input-group-btn">
                <button class="btn btn-default btn-sm" type="submit"><i class="ti-arrow-right"></i></button>
            </span>
        </div>
    </form>
    <br>
    <br>
    <br>
    <div class="chat-box" id="chat_box"></div>

    <script>
        var chat_messager = {
            input: null,
            form: null,
            send_message: function () {
                var msg = chat_messager.input.val();
                chat_messager.input.val('');
                chat_messager.block();

                $.post(chat_messager.form.attr('action') + '&ajax', {
                    to_user_id: <?= $from_user_id ?>,
                    message: msg
                }, function (data) {
                    setTimeout(function () {
                        chat_messager.unblock();

                        // Request messages for Toaster
                        chat_messager.check_for_new_messages();
                    }, 300);
                });
            },
            block: function () {
                chat_messager.input.prop('disabled', true);
            },
            unblock: function () {
                chat_messager.input.prop('disabled', false).focus();
            },
            check_for_new_messages: function () {
                $('#chat_box').load('?p=<?= P ?>&do=_ajax_get_messages&user_id=<?= $from_user_id ?>');

                clearTimeout(chat_messager.timer);
                chat_messager.timer = setTimeout(function () {
                    chat_messager.check_for_new_messages();
                }, 10000);
            },
            timer: 0
        };

        chat_messager.form = $('#send-message-form');
        chat_messager.input = $('#chat_message');

        chat_messager.form.submit(function (e) {
            e.preventDefault();

            chat_messager.send_message();
        });

        chat_messager.check_for_new_messages();
    </script>
<?php