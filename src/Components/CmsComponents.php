<?php

namespace TMCms\Admin\Components;

use TMCms\Admin\Structure\Object\StructurePageCollection;
use TMCms\HTML\Cms\CmsTable;
use TMCms\HTML\Cms\Column\ColumnTree;
use TMCms\Routing\Structure;
use TMCms\Templates\Page;
use TMCms\Traits\singletonInstanceTrait;

defined('INC') or exit;

class CmsComponents
{
    use singletonInstanceTrait;

    /**
     * WYSIWYG rich text editor
     */
    public function wysiwyg()
    {
        ob_start();
        ?>
        <textarea id="wysiwyg_<?= NOW ?>" name="wysiwyg_<?= NOW ?>"></textarea>
        <input class="btn btn-primary" type="button" onclick="done()" value="Update">

        <script>
            resize_wysiwyg = function () {
                var $block = $('.cke_wrapper');
                var $block_in = $('#cke_contents_wysiwyg_<?= NOW ?>');
                var $win = $('#modal-popup_inner');

                $block.height($win.height() - 70);
                $block_in.height($win.height() - 170);
            };

            function done() {
                popup_modal.result_element.val(editor.getData());
                popup_modal.result_element.focus();
                popup_modal.close();
                return true;
            }

            var value = popup_modal.result_element.val();
            $('#wysiwyg_<?= NOW ?>').html(value);

            var editor = CKEDITOR.replace('wysiwyg_<?= NOW ?>',
                {
                    filebrowserBrowseUrl: '<?= DIR_CMS_URL ?>?p=filemanager&nomenu&allowed_extensions=jpg,jpeg,bmp,tiff,tif,gif&cache=<?= NOW ?>'
                }
            );

            setInterval(function () {
                $('.cke_dialog_background_cover').remove();
            }, 2000);

            setTimeout(function () {
                resize_wysiwyg();
            }, 1000);
            $(window).resize(function () {
                resize_wysiwyg();
            });
        </script>
        <br><?php
        echo ob_get_clean();
        die;
    }

    /**
     * Action for WYSIWYG, set text in parent input
     */
    public function _wysiwyg()
    {
        if (!isset($_POST['wysiwyg'])) {
            return;
        }
        ?>
        <script type="text/javascript">
            var $el = window.opener.$('#' + window.opener.resultOutputID);
            $el.val('<?= $_POST['wysiwyg'] ?>').focus();
            window.close();
        </script><?php
    }

    /**
     * Calendar Widget using old pop-up window with customizible format
     */
    public function calendar()
    {
        Page::getHead()
            ->addJsURL('jscalendar/calendar.js')
            ->addCssURL('jscalendar/style/theme.css');

        $date_format = '%Y-%m-%d';
        // Custom format
        if (!empty($_GET['format'])) {
            $date_format = $_GET['format'];
        }
        ?>
        <table>
            <tr>
                <td>
                    <div id="calendar-container"></div>
                    <input type="hidden" id="date_field" value="">
                </td>
            </tr>
            <tr>
                <td align="right">
                    <br>
                    <input type="button" id="done_button" onclick="submitDate()" value="Done">
                    &nbsp;&nbsp;&nbsp;
                    <input type="button" value="<?= __('Cancel') ?>" onclick="window.close()">
                </td>
            </tr>
        </table>

        <script type="text/javascript">
            function submitDate() {
                var $el = window.opener.$('#' + window.opener.resultOutputID);
                $el.val($('#date_field').val()).focus();
                window.close();
            }
            function dateChanged(calendar) {
                $('#date_field').val((new Date(calendar.date)).print('<?= $date_format ?>'));
            }
            function getCurDate() {
                var current_date = false;
                if (window.opener && window.opener.resultOutputID) {
                    var $el = window.opener.$('#' + window.opener.resultOutputID);
                    if ($el.val() != '') {
                        current_date = Date.parseDate($el.val(), '<?= $date_format ?>');
                    }
                }
                if (!current_date) {
                    current_date = new Date();
                }

                return current_date;
            }

            // Start calendar
            Calendar.setup({
                flat: 'calendar-container',
                date: getCurDate(),
                flatCallback: dateChanged,
                ifFormat: '<?= $date_format ?>',
                firstDay: 1,
                showsTime: <?= (int)(!empty($_GET['showtime'])) ?>
            });
        </script><?php
    }

    /**
     * Site Structure pages Widget
     */
    public function pages()
    {
        $data = [];

        $pages = new StructurePageCollection();
        $pages->addOrderByField();

        foreach ($pages->getAsArrayOfObjectData() as $v) {
            // Main tree page
            if (!$v['pid'] && $v['active'] && $v['in_menu']) {
                $v['title'] = '<strong>' . $v['title'] . '</strong>';
            }
            // Make link
            $v['title'] = '<a style="cursor:pointer" onclick="popup_modal.result_element.val(\'' . Structure::getPathById($v['id'], false) . '\'); popup_modal.result_element.focus(); popup_modal.close(); return false;">' . $v['title'] . ' (' . $v['location'] . ')</a>';

            $data[] = $v;
        }

        ob_clean();
        echo CmsTable::getInstance()
            ->addData($data)
            ->disablePager()
            ->addColumn(ColumnTree::getInstance('id')
                ->setTitle('Page')
                ->setShowKey('title')
                ->allowHtml()
            );
        echo ob_get_clean();

        die;
    }
}