<?php

namespace TMCms\Admin\Components;

use TMCms\Admin\Structure\Entity\PageEntityRepository;
use TMCms\Files\Finder;
use TMCms\HTML\Cms\CmsTable;
use TMCms\HTML\Cms\CmsTabs;
use TMCms\HTML\Cms\Column\ColumnData;
use TMCms\HTML\Cms\Column\ColumnTree;
use TMCms\Orm\Entity;
use TMCms\Orm\EntityRepository;
use TMCms\Routing\Structure;
use TMCms\Strings\Converter;
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
        <style>
            .tinymce-container, .tinymce {
                height: 100%;
            }

            .mce-btn-group:last-child {
                float: right;
                border-left: none;
                border-left: none;
            }
        </style>
        <div class="tinymce-container">
            <textarea name="tinymce" class="tinymce" id="tinymce_<?= NOW ?>"></textarea>
        </div>
        <script>
            $(function () {
                var originalTextarea = $('<?= isset($_GET['selector']) ? '#' . $_GET['selector'] : '' ?>');

                tinyMCE.PluginManager.add('stylebuttons', function (editor, url) {
                    ['pre', 'p', 'code', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'].forEach(function (name) {
                        editor.addButton("style-" + name, {
                            tooltip: "Toggle " + name,
                            text: name.toUpperCase(),
                            onClick: function () {
                                editor.execCommand('mceToggleFormat', false, name);
                            },
                            onPostRender: function () {
                                var self = this, setup = function() {
                                    editor.formatter.formatChanged(name, function(state) {
                                        self.active(state);
                                    });
                                };
                                editor.formatter ? setup() : editor.on('init', setup);
                            }
                        })
                    });
                });

                tinyMCE.init({
                    selector: 'textarea#tinymce_<?= NOW ?>',
                    element_format: 'html',
                    relative_urls: false,
                    image_caption: true,
                    menubar: false,
                    statusbar: true,
                    plugins: ['stylebuttons', 'textcolor', 'colorpicker', 'media', 'table', 'contextmenu', 'image', 'link', 'hr', 'code', 'paste', 'save'],
                    toolbar: ['undo redo | styleselect | bold italic underline | style-h1 style-h2 style-h3 style-p hr | alignleft aligncenter alignright alignjustify | forecolor backcolor | bullist numlist | table link image media code | save close'],
                    content_css: '<?= DIR_ASSETS_URL . 'stylesheets/admin/tinymce.css' ?>', // TODO: Dynamic content_css loading
                    setup: function (editor) {
                        editor.addButton('close', {
                            text: 'Close',
                            icon: false,
                            onclick: function () {
                                $('[id^="' + editor.id + '"]').parents('#modal-popup_inner').trigger('popup:close');
                            }
                        });
                    },
                    init_instance_callback: function (editor) {
                        editor.setContent(originalTextarea.val());
                    },
                    save_onsavecallback: function (editor) {
                        originalTextarea.val(editor.getContent());
                    },
                    file_picker_callback: function (callback, value, meta) {
                        if (meta.filetype == 'image') {
                            var imageModal = new PopupModal({
                                url: '?p=filemanager&nomenu&allowed_extensions=jpg,jpeg,bmp,tiff,tif,gif&cache=<?= NOW ?>'
                            });

                            imageModal.show();
                            imageModal.onReturnResult(function (result) {
                                var modalWindow = $('.mce-window[aria-label="Insert/edit image"]');
                                var imageObject = new Image();
                                var imagePath = result;

                                imageObject.onload = function () {
                                    modalWindow.find('label:contains("Source")').next().find('input').val(imagePath);

                                    modalWindow.find('input.mce-textbox[aria-label="Width"]').val(this.width);
                                    modalWindow.find('input.mce-textbox[aria-label="Height"]').val(this.height);
                                };

                                imageObject.src = window.location.protocol + '//' + window.location.host + imagePath;
                            });
                        }
                    },
                    formats: {
                        alignleft: { selector: 'img', styles: { 'float': 'left', 'margin': '0 1rem 1rem 0' } },
                        alignright: { selector: 'img', styles: { 'float': 'right', 'margin': '0 0 1rem 1rem' } }
                    }
                });
            });
        </script>
        <?php
        echo ob_get_clean();
        die;
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
        $pages = new PageEntityRepository();
        $pages->addOrderByField();

        foreach ($pages->getAsArrayOfObjectData() as $v) {
            // Main tree page
            if (!$v['pid'] && $v['active'] && $v['in_menu']) {
                $v['title'] = '<strong>' . $v['title'] . '</strong>';
            }
            // Make link
            $v['title'] = '<a style="cursor:pointer" onclick="selectLinkForSitemap(\'' . Structure::getPathById($v['id'], false) . '\'); return false;">' . $v['title'] . ' (' . $v['location'] . ')</a>';

            $data[] = $v;
        }

        ob_clean();
        ?>
        <script>
            function selectLinkForSitemap(page_id) {
                var modalWindow = $('#modal-popup_inner');
                modalWindow.trigger('popup:return_result', [page_id]);
                modalWindow.trigger('popup:close');
            }
        </script>
        <?php
        $structure_table = CmsTable::getInstance()
            ->addData($data)
            ->disablePager()
            ->addColumn(ColumnTree::getInstance('id')
                ->setTitle('Page')
                ->setShowKey('title')
                ->allowHtml()
            );

        $tabs = new CmsTabs;
        $tabs->addTab('Structure', $structure_table, true);

        foreach (Finder::getInstance()->getEntitiesWithSitemapLinks() as $entity) {
            $entity_class = get_class($entity);
            $repo_class = $entity_class . 'Repository';
            /** @var EntityRepository $entity_repository */
            $entity_repository = new $repo_class;

            if (method_exists($entity_repository, 'getLinksForSitemap')) {
                // May be implemented in repository
                $table = $entity_repository->getLinksForSitemap();
            } else {
                // Or auto generated with entities
                $data = [];
                /** @var Entity $obj */
                foreach ($entity_repository->getAsArrayOfObjects() as $obj) {
                    $data[] = [
                        'link' => '<a style="cursor:pointer" onclick="selectLinkForSitemap(\'' . $obj->getLinkForSitemap() . '\'); return false;">' . $obj->getLinkForSitemap() . '</a>',
                    ];
                }

                $table = CmsTable::getInstance()
                    ->addData($data)
                    ->disablePager()
                    ->addColumn(ColumnData::getInstance('link')
                        ->allowHtml()
                    );
            }

            $tabs->addTab(rtrim(Converter::classWithNamespaceToUnqualifiedShort($entity), 'Entity'), $table);
        }

        echo $tabs;
        echo ob_get_clean();

        die;
    }

    /**
     * Google Map to select point point and coordinates
     */
    public function google_map()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    /**
     * SVG Map to select polygon from image file
     */
    public function svg_map()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }
}