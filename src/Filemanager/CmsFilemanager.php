<?php

namespace TMCms\Admin\Filemanager;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use TMCms\Admin\Messages;
use TMCms\Admin\Users;
use TMCms\Files\FileSystem;
use TMCms\Files\Image;
use TMCms\HTML\Cms\CmsFieldset;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\Element\CmsButton;
use TMCms\HTML\Cms\Element\CmsCheckbox;
use TMCms\HTML\Cms\Element\CmsHtml;
use TMCms\HTML\Cms\Element\CmsInputHidden;
use TMCms\HTML\Cms\Element\CmsInputText;
use TMCms\HTML\Cms\Element\CmsRadioBox;
use TMCms\HTML\Cms\Element\CmsTextarea;
use TMCms\Log\App;
use TMCms\Strings\Converter;
use TMCms\Strings\UID;
use TMCms\Traits\singletonInstanceTrait;
use ZipArchive;

defined('INC') or exit;

class CmsFilemanager
{
    use singletonInstanceTrait;

    /**
     * Main view
     */
    public function _default()
    {
        // We can show only files if user is locked to current folder
        $files_only = isset($_GET['files_only']);

        // We can set range of allowed file extensions to be uploaded
        $allowed_extensions = isset($_GET['allowed_extensions']) ? explode(',', $_GET['allowed_extensions']) : [];

        // If page is shown in modal widow
        if (IS_AJAX_REQUEST) {
            ob_start();
        }

        // Directory to be shown is supplied from url
        $dir = isset($_GET['path']) ? $_GET['path'] : NULL;

        // Maybe user hav access only to public folder, so we have to check and change folder
        if (Users::getInstance()->getGroupData('filemanager_limited') && stripos($dir, DIR_PUBLIC_URL) === false) {
            $dir = DIR_PUBLIC_URL;
        }

        // If no dir - set default directory for public files
        if (!$dir) {
            $dir = DIR_PUBLIC_URL;
        }
        // Check that we have no slash as first symbol
        if ($dir[0] === '/') {
            $dir = substr($dir, 1);
        }
        // Create requested directory if not exists
        if (!file_exists(DIR_BASE . $dir)) {
            FileSystem::mkDir(DIR_BASE . $dir);
        }

        // Create list of folders and files in requested location
        $file_list = $dir_list = [];
        foreach (array_diff(scandir(DIR_BASE . $dir), ['.', '..']) as $v) {
            $p = $dir . $v;
            if (is_dir(DIR_BASE . $p)) {
                $dir_list[] = $p . '/';
            } else {
                $file_list[] = $p;
            }
        }

        // Generate path to upper folder
        $tmp = [];
        $path_up = explode('/', $dir);
        foreach ($path_up as $v) {
            if ($v) {
                $tmp[] = $v;
            }
        }
        $path_up = $tmp;
        array_pop($path_up);
        $path_up = '/' . implode('/', $path_up) . '/';

        // Show current path and generate links on page parts
        $path_links = [];
        $tmp = '/';
        foreach (explode('/', $dir) as $v) {
            if (trim($v) == '') {
                continue;
            }
            $tmp .= $v . '/';
            $path_links[] = '<a onclick="filemanager_helpers.loadDirectory(this); return false;" href="?p=' . P . '&nomenu&path=' . $tmp . '">' . $v . '</a>';
        }

        // Show top bar if we are allowed to view folders
        if (!$files_only):
            ?>
            <!--suppress JSUnresolvedFunction -->
            <div style="padding: 10px; position: relative">
                <a onclick="filemanager_helpers.show_create_directory(); return false;" href="?p=<?= P ?>&do=create_directory&nomenu&path=<?= $dir ?>">Create directory</a>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <a href="?p=<?= P ?>&do=filemanager&nomenu&path=<?= $path_up ?>" onclick="filemanager_helpers.loadDirectory(this); return false;">Go up</a>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                Current path: /<?= implode('/', $path_links) ?>
                <hr>
                <a onclick="filemanager_helpers.show_create_file(); return false;" href="?p=<?= P ?>&do=create_file&nomenu&path=<?= $dir ?>">Create file</a>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                Filter by name&nbsp;&nbsp;<input type="text" id="filter_name" placeholder="File or folder name">
                <hr>
                <span id="multiple_commands">
                    <var onclick="multiple.download(this)"><?= __('Download') ?></var>
                    &nbsp;&nbsp;
                    <var onclick="if (confirm('<?= __('Are you sure?') ?>')) multiple.delete_files()"><?= __('Delete') ?></var>
                    &nbsp;&nbsp;
                    <var onclick="multiple.copy(this)"><?= __('Copy') ?></var>
                    &nbsp;&nbsp;
                    <var id="multiple_paste" style="display:none;" onclick="multiple.paste(this)"><?= __('Paste') ?></var>
                </span>
                <hr>
                <div style="position: absolute; top: 0; right: 0; width: 300px; z-index: 10">
                    <img width="300" id="filemanager_current_image" style="display: none" src="<?= DIR_CMS_IMAGES_URL ?>_.gif">
                </div>
            </div>
        <?php endif; ?>

        <div style="min-height: 350px; overflow-y: auto; padding: 10px" id="file_list_zone">
            <table cellspacing="0" cellpadding="0" style="line-height:20px">
                <?php if (!$files_only): ?>
                    <tr>
                        <td width="100%"></td>
                        <td></td>
                        <td width="75"></td>
                        <td width="30"></td>
                    </tr>
                    <tr>
                        <td colspan="5">Folders:</td>
                    </tr>
                    <?php foreach ($dir_list as $k => $v): ?>
                        <?php if ($v[0] != '/') $v = '/' . $v ?>
                        <tr class="bg_mouseover toggle_checkbox">
                            <td>
                                <input class="cb_hide" type="checkbox" name="<?= $v ?>" value="">
                                &nbsp;
                                <a class="dir_context" href="?p=<?= P ?>&do=filemanager&nomenu&path=<?= $v ?>" onclick="return setSelectedToInput(this);" data-path="<?= $v ?>" ondblclick="filemanager_helpers.loadDirectory(this); return false;" data-name="<?= basename($v) ?>"><?= basename($v) ?></a>
                            </td>
                            <td></td>
                            <td></td>
                            <td align="center">
                                <a href="?p=<?= P ?>&do=_delete&path=<?= $v ?>" onclick="filemanager_helpers.delete_files('<?= $v ?>'); return false;">x</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="5">&nbsp;</td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="5">Files:</td>
                </tr>
                <?php foreach ($file_list as $k => $v):
                    // Need to have slash in name to set absolute paths
                    if ($v[0] != '/') {
                        $v = '/' . $v;
                    }

                    // Check special file type to show context menu
                    $ext = strtolower(pathinfo($v, PATHINFO_EXTENSION));
                    $type_by_extension = '';
                    if (in_array($ext, ['txt', 'html', 'php', 'js', 'htaccess', 'css', ''])) {
                        $type_by_extension = '_text';
                    } elseif (in_array(strtolower($ext), ['bmp', 'jpg', 'png', 'jpeg', 'gif'])) {
                        $type_by_extension = '_img';
                    } elseif (in_array(strtolower($ext), ['zip'])) {
                        $type_by_extension = '_archive';
                    }
                    ?>
                    <tr class="bg_mouseover toggle_checkbox">
                        <td>
                            <input class="cb_hide" type="checkbox" name="<?= $v ?>" value="">
                            &nbsp;
                            <a class="file_context<?= $type_by_extension ?>" href="" onclick="return setSelectedToInput(this);" data-path="<?= $v ?>" ondblclick="done();"
                                <?php if ($type_by_extension == '_img'): ?>
                                    onmouseover="$('#filemanager_current_image').attr('src', '<?= $v ?>').show()"
                                    onmouseout="$('#filemanager_current_image').attr('src', '<?= DIR_CMS_IMAGES_URL ?>_.gif').hide()"
                                <?php endif; ?>
                                data-name="<?= basename($v) ?>"><?= basename($v) ?></a>
                        </td>
                        <td></td>
                        <td><?= Converter::formatDataSize(filesize(DIR_BASE . $v)) ?></td>
                        <td align="center">
                            <a href="?p=<?= P ?>&do=_delete&path=<?= $v ?>" onclick="filemanager_helpers.delete_files('<?= $v ?>'); return false;">x</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>




            <?php if ($files_only) {
                // Stop further rendering if we can locked to see only files
                echo ob_get_clean();
                die;
            } ?>

            <?php // These are context menus for different types of files ?>

            <ul id="menu_dir" class="contextMenu">
                <li><a href="#dir_rename">Rename</a></li>
                <li><a href="#dir_delete">Delete folder</a></li>
                <li><a href="#dir_delete_content">Remove content</a></li>
            </ul>

            <ul id="menu_file" class="contextMenu">
                <li><a href="#file_view">View</a></li>
                <li><a href="#file_rename">Rename</a></li>
                <li><a href="#file_delete">Delete file</a></li>
            </ul>

            <ul id="menu_text" class="contextMenu">
                <li><a href="#file_view">Open file</a></li>
                <li><a href="#file_rename">Rename</a></li>
                <li><a href="#file_edit_content">Edit content</a></li>
                <li><a href="#file_delete">Delete file</a></li>
            </ul>

            <ul id="menu_img" class="contextMenu">
                <li><a href="#file_view">Preview image</a></li>
                <li><a href="#file_rename">Rename</a></li>
                <li><a href="#file_delete">Delete file</a></li>
            </ul>

            <ul id="menu_archive" class="contextMenu">
                <li><a href="#file_download">Download</a></li>
                <li><a href="#file_rename">Rename</a></li>
                <li><a href="#file_delete">Delete file</a></li>
            </ul>

            <div id="con_bg"></div>
            <div id="con_bg_in">
                <div id="con_rename">
                    <?php
                    // Show form to rename file or folder
                    echo CmsForm::getInstance()
                        ->enableAjax()
                        ->setAction('?p=' . P . '&do=_edit&action=rename&path=' . $dir)
                        ->setSubmitButton(new CmsButton('Rename'))
                        ->addField('Type', CmsHTML::getInstance('type'))
                        ->addField('Type of', CmsInputHidden::getInstance('type_of_1'))
                        ->addField('Current', CmsHTML::getInstance('current_name'))
                        ->addField('Current path', CmsInputHidden::getInstance('current_path'))
                        ->addField('New', CmsInputText::getInstance('new_name')
                            ->disableBackupBlock()
                        );
                    ?>
                    <script>
                        $('#con_rename').find('input[type=submit]').click(function() {
                            setTimeout(function() {
                                _.con.close();
                                $('#con_rename').hide();
                                filemanager_helpers.reloadFiles();
                            }, 1000);
                        });
                    </script>
                </div>
                <div id="con_delete">
                    <?php
                    // Show form to delete file or folder with content
                    echo CmsForm::getInstance()
                        ->enableAjax()
                        ->setAction('?p=' . P . '&do=_edit&action=delete&path=' . $dir)
                        ->addField('Type of', CmsInputHidden::getInstance('type_of_2'))
                        ->addField('Current path', CmsInputHidden::getInstance('remove_path'))
                        ->addField('Leave folder', CmsInputHidden::getInstance('leave_folder'));
                    ?>
                    <script>
                        $('#con_delete').find('input[type=submit]').click(function() {
                            setTimeout(function() {
                                _.con.close();
                                $('#con_delete').hide();
                                filemanager_helpers.reloadFiles();
                            }, 1000);
                        });
                    </script>
                </div>
                <div id="con_content">
                    <?php
                    // Show folder to edit file content
                    echo CmsForm::getInstance()
                        ->enableAjax()
                        ->setAction('?p=' . P . '&do=_edit&action=content&path=' . $dir)
                        ->setSubmitButton(new CmsButton('Save'))
                        ->addField('Type of', CmsInputHidden::getInstance('type_of_3'))
                        ->addField('Current path', CmsInputHidden::getInstance('file_name'))
                        ->addField('Content', CmsHTML::getInstance('content_loading')
                            ->setValue('Loading...'))
                        ->addField('Content', CmsTextarea::getInstance('content')
                            ->disableBackupBlock()
                            ->setRowCount(10)
                        );
                    ?>
                    <script>
                        $('#con_content').find('input[type=submit]').on('click', function() {
                            setTimeout(function() {
                                _.con.close();
                                $('#con_content').hide();
                                filemanager_helpers.reloadFiles();
                            }, 1000);
                        });
                    </script>
                </div>
                <div id="con_dir_create">
                    <?php
                    // Show form for create folder
                    echo CmsForm::getInstance()
                        ->enableAjax()
                        ->setAction('?p=' . P . '&do=_edit&action=dircreate&path=' . $dir)
                        ->setSubmitButton(new CmsButton('Create Directory'))
                        ->addField('Directory name', CmsInputText::getInstance('dirname')
                            ->disableBackupBlock()
                        )
                    ?>
                    <script>
                        $('#con_dir_create').find('input[type=submit]').click(function() {
                            setTimeout(function() {
                                _.con.close();
                                $('#con_dir_create').hide();
                                filemanager_helpers.reloadFiles();
                            }, 1000);
                        });
                    </script>
                </div>
                <div id="con_file_create">
                    <?php
                    // Shoe form for create file with content
                    echo CmsForm::getInstance()
                        ->enableAjax()
                        ->setAction('?p=' . P . '&do=_edit&action=filecreate&path=' . $dir)
                        ->setSubmitButton(new CmsButton('Create File'))
                        ->addField('File name', CmsInputText::getInstance('file_name'))
                        ->addField('Content', CmsTextarea::getInstance('content')
                            ->disableBackupBlock()
                            ->setRowCount(10));
                    ?>
                    <script>
                        $('#con_file_create').find('input[type=submit]').click(function() {
                            setTimeout(function() {
                                _.con.close();
                                $('#con_file_create').hide();
                                filemanager_helpers.reloadFiles();
                            }, 1000);
                        });
                    </script>
                </div>
            </div>
        </div>
        <br>
        <div style="margin: auto 5px">
        <?php
        // Simple upload form
        echo CmsForm::getInstance()
            ->addField('Selected file (<a href="" onclick="done(); return false">Set</a>)',
                CmsInputText::getInstance('filename')
                    ->disableBackupBlock()
                    ->enableReadOnly()
            )
        ;
        ?>
        </div>
        <?php

        // Get maximum allowed size of chunk of uploaded file
        $max_upload_file = $max_post = Converter::formatIniSize(ini_get('post_max_size'));
        $max_upload = Converter::formatIniSize(ini_get('upload_max_filesize'));
        if ($max_upload < $max_upload_file) {
            $max_upload_file = $max_upload;
        }

        // Modern upload form with multiple file selects and large file uploads
        $upload_form = CmsForm::getInstance()
            ->setSubmitButton(CmsButton::getInstance('Upload')
                ->setElementIdAttribute('upload_files'))
            ->setEnctype(CmsForm::ENCTYPE_MULTIPART)
            ->setAction('?p=' . P . '&do=_upload')
            ->addField('Upload files', CmsHtml::getInstance('file')
                ->enableMultiple()
                ->setValue('
        <div id="filelist">
            <input id="file" type="file" name="file[]" class="form-control" multiple="">
        </div>
        <div id="container">
            <a id="pickfiles" href="javascript:;" style="display: block; padding-top: 2px; height: 23px; font-size: 13px; text-align: center; border: 1px solid black">Click to select files, or drag files here</a>
        </div>
        <pre id="console" style="display: none"></pre>')
            )
            ->addField('Extract .zip files', CmsCheckbox::getInstance('extract')
                ->setIsChecked()
            )
            ->addField('If file exists',
                CmsRadioBox::getInstance('exists')
                    ->setRadioButtons(['skip' => 'Skip upload', 'overwrite' => 'Overwrite', 'rename' => 'Make new name'])
                    ->setSelected('skip')
            )
        ;

        // Render textarea with form
        echo $upload_form;
        ?>

        <script>
            // Choose folder or file
            function setSelectedToInput(link) {
                $('a[data-path="' + link.getAttribute('data-path') + '"')
                    .parents('#modal-popup_inner')
                    .find('#filename')
                    .val(link.getAttribute('data-path'));

                return false;
            }
            // Set value in opener and close window
            function done() {
                var filenameInput = $('#filename'),
                    modalWindow = filenameInput.parents('#modal-popup_inner');

                modalWindow.trigger('popup:return_result', [filenameInput.val()]);
                modalWindow.trigger('popup:close');

                // use CKEditor 3.0 integration method
//                if ('<?//= (int) isset($_GET['CKEditor']) ?>//' == '1') {
//                    window.opener.CKEDITOR.tools.callFunction('<?//= isset($_GET['CKEditorFuncNum']) ? $_GET['CKEditorFuncNum'] : '' ?>//',filenameInput.val());
//                    window.close();
//                }
                // Does not know which element activated
//                if (!window.opener || typeof window.opener.resultOutputID == 'undefined' || !window.opener.resultOutputID) {
//                    alert('No field in opener window found');
//                    return;
//                }
//
//                var el = opener.$('#' + window.opener.resultOutputID);
//
//                // Set value
//                if (el) {
//                    el.val(filenameInput.val());
//                }
            }

            // Bind context menu on files and folder - needs to be launched every page reload
            function init_context_events_on_files() {

                // Folder context menu
                $(".dir_context").contextMenu({
                    menu: "menu_dir"
                }, function (action, el) {
                    var $el = $(el);
                    switch (action) {
                        // Rename folder
                        case 'dir_rename':
                            _.con.open();
                            $('#con_rename').show();
                            $('#type_data').find('td:last').html('Folder');
                            $('#current_name_data').find('td:last').html($el.attr('data-name'));
                            $('#new_name').val($el.attr('data-name'));
                            $('#current_path').val($el.attr('data-path'));
                            $('#type_of_1').val('folder');
                            break;
                        //  Delete folder with content
                        case 'dir_delete':
                            if (confirm('<?= __('Are you sure?') ?>')) {
                                $('#remove_path').val($el.attr('data-path'));
                                $('#type_of_2').val('folder');
                                $('#leave_folder').val(0).closest('form').submit();
                            }
                            break;
                        // Delete folder content and leave folder itself
                        case 'dir_delete_content':
                            if (confirm('<?= __('Are you sure?') ?>')) {
                                $('#remove_path').val($el.attr('data-path'));
                                $('#type_of_2').val('folder');
                                $('#leave_folder').val(1).closest('form').submit();
                            }
                            break;
                    }
                });

                // File context menu
                $(".file_context").contextMenu({
                    menu: "menu_file"
                }, function (action, el) {
                    var $el = $(el);
                    $('#type_of').val('file');
                    switch (action) {
                        // Open file in new window
                        case 'file_view':
                            window.open($el.attr('data-path'));
                            break;
                        // Rename file
                        case 'file_rename':
                            _.con.open();
                            $('#con_rename').show();
                            $('#type_data').find('td:last').html('File');
                            $('#current_name_data').find('td:last').html($el.attr('data-name'));
                            $('#new_name').val($el.attr('data-name'));
                            $('#current_path').val($el.attr('data-path'));
                            $('#type_of_1').val('file');
                            break;
                        // Delete file
                        case 'file_delete':
                            if (confirm('<?= __('Are you sure?') ?>')) {
                                $('#type_of_2').val('file');
                                $('#remove_path').val($el.attr('data-path').closest('form').submit());
                            }
                            break;
                    }
                });

                // Text editable file context menu
                $(".file_context_text").contextMenu({
                    menu: "menu_text"
                }, function (action, el) {
                    var $el = $(el);
                    $('#type_of').val('file');
                    switch (action) {
                        // Open text file
                        case 'file_view':
                            window.open($el.attr('data-path'));
                            break;
                        // Rename file
                        case 'file_rename':
                            _.con.open();
                            $('#con_rename').show();
                            $('#type_data').find('td:last').html('File');
                            $('#current_name_data').find('td:last').html($el.attr('data-name'));
                            $('#new_name').val($el.attr('data-name'));
                            $('#current_path').val($el.attr('data-path'));
                            $('#type_of_1').val('file');
                            break;
                        // Edit file content
                        case 'file_edit_content':
                            _.con.open();
                            $('#con_bg_in').width(600).height(600).css('margin-left', -300);
                            $('#con_content, #content_loading_data').show();
                            $('#type_of_3').val('file');
                            $('#file_name').val($el.attr('data-path'));
                            // Ajax for file content
                            $.ajax({
                                url: '?p=<?=P?>&do=_get_file_content&path=' + $el.attr('data-path'),
                                success: function (data) {
                                    $('#content').val(data);
                                    $('#content_loading_data').hide();
                                }
                            });
                            break;
                        // Delete file
                        case 'file_delete':
                            if (confirm('<?= __('Are you sure?') ?>')) {
                                $('#type_of_2').val('file');
                                $('#remove_path').val($el.attr('data-path').closest('form').submit());
                            }
                            break;
                    }
                });

                // Image file context menu
                $(".file_context_img").contextMenu({
                    menu: "menu_img"
                }, function (action, el) {
                    var $el = $(el);
                    $('#type_of').val('file');
                    switch (action) {
                        // Open image in new window
                        case 'file_view':
                            window.open($el.attr('data-path'));
                            break;
                        // Rename image
                        case 'file_rename':
                            _.con.open();
                            $('#con_rename').show();
                            $('#type_data').find('td:last').html('File');
                            $('#current_name_data').find('td:last').html($el.attr('data-name'));
                            $('#new_name').val($el.attr('data-name'));
                            $('#current_path').val($el.attr('data-path'));
                            $('#type_of_1').val('file');
                            break;
                        // Delete image
                        case 'file_delete':
                            if (confirm('<?= __('Are you sure?') ?>')) {
                                $('#type_of_2').val('file');
                                $('#remove_path').val($el.attr('data-path')).closest('form').submit();
                            }
                            break;
                    }
                });

                // Archive file context menu
                $(".file_context_archive").contextMenu({
                    menu: "menu_archive"
                }, function (action, el) {
                    var $el = $(el);
                    $('#type_of').val('file');
                    switch (action) {
                        // Download archive
                        case 'file_download':
                            window.open($el.attr('data-path'));
                            break;
                        // Rename archive
                        case 'file_rename':
                            _.con.open();
                            $('#con_rename').show();
                            $('#type_data').find('td:last').html('File');
                            $('#current_name_data').find('td:last').html($el.attr('data-name'));
                            $('#new_name').val($el.attr('data-name'));
                            $('#current_path').val($el.attr('data-path'));
                            $('#type_of_1').val('file');
                            break;
                        // Delete archive
                        case 'file_delete':
                            if (confirm('<?= __('Are you sure?') ?>')) {
                                $('#type_of_2').val('file');
                                $('#remove_path').val($el.attr('data-path')).closest('form').submit();
                            }
                            break;
                    }
                });
            }

            // Bind context events every time
            $(document).ready(function () {
                init_context_events_on_files();
            });

            // Main Container
            var _ = {
                con: {
                    close: function () {
                        $('#con_bg_in').hide().width(400).height(200).css('margin-left', -200);
                        $('#con_bg, #con_bg_in, #con_rename, #con_file_create, #con_dir_create').hide();
                    },
                    open: function () {
                        var $w = $(window);
                        var w_h = $w.height();
                        $('#con_bg').show().width('100%').height(w_h);
                        $('#con_bg_in').show();
                    }
                }
            };

            // Bind checkbox events
            function events_on_checkboxes() {
                // Checkboxes toggle
                $('.toggle_checkbox input').change(function () {
                    var $container = $('#multiple_commands');
                    if ($('.toggle_checkbox input:checked').length > 0) {
                        $container.show();
                    } else {
                        $container.hide();
                    }
                });

                $('#con_bg').click(function () {
                    _.con.close();
                });
            }
            events_on_checkboxes();

            // Filter by name
            $('#filter_name').focus().keyup(function (el) {
                var value = el.target.value;
                if (!value.length) {
                    $('a[data-name').closest('tr').show();
                } else {
                    $('a[data-name').closest('tr').hide();
                }

                $('a[data-name*="' + value + '"]').closest('tr').show();
            });

            // Actions with multiple selected items
            var multiple = {
                // Start storage
                init: function () {
                    var storage = this.getLocalStorage();
                    var items_copy = storage.get('multiple_copy_items');

                    if (items_copy) {
                        $('#multiple_paste').show();
                    }
                },
                // Get list of selected items
                get_selected_item_sources: function () {
                    var items = [];
                    $('.toggle_checkbox input:checked').each(function (k, v) {
                        items.push($(v).attr('name'));
                    });
                    return items;
                },
                // Show / hide available button
                toggleButton: function (el) {
                    $(el).stop().fadeOut('fast', function () {
                        $(el).fadeIn('fast');
                    });
                },
                getLocalStorage: function () {
                    return new Storage('filemanager_multiple');
                },
                // Delete files from server
                delete_files: function () {
                    var items = this.get_selected_item_sources();

                    $.post('?p=filemanager&nomenu&do=_multiple_delete&ajax' + Date.now(),
                        {pathes: items},
                        function () {
                            filemanager_helpers.reloadFiles();
                        });
                },
                // Delete files from server
                download: function () {
                    var items = this.get_selected_item_sources();

                    $.post('?p=filemanager&nomenu&do=_multiple_download&ajax' + Date.now(),
                        {pathes: items},
                        function (link) {
                            location.href = link;
                        });
                },
                // Copy selected items
                copy: function (el) {
                    // Save item paths into buffer
                    var items = this.get_selected_item_sources();
                    var storage = this.getLocalStorage();
                    storage.set('multiple_copy_items', items);
                    this.toggleButton(el);
                    this.init();
                },
                // Paste (create copied)
                paste: function (el) {
                    var storage = this.getLocalStorage();

                    // Check copied items
                    var items = storage.get('multiple_copy_items');
                    if (items) {
                        $.get('?p=filemanager&nomenu&do=_multiple_copy&ajax' + Date.now(),
                            {pathes: items, current_path: '<?= $dir ?>'},
                            function () {
                                filemanager_helpers.reloadFiles();
                            }
                        );
                    }
                    this.toggleButton(el);
                }
            };
            multiple.init();

            // File uploader plugin with chunk upload possibility, drag and drop, and multiple selectes
            var uploader = new plupload.Uploader({
                runtimes: 'html5,flash,silverlight,html4',
                browse_button: 'pickfiles',
                max_retries: 3,
                chunk_size: '<?= $max_upload_file ?>',
                container: document.getElementById('container'),
                drop_element: document.getElementById('container'),
                url: "?p=<?= P ?>&do=_upload_multiple&path=<?= $dir ?>&allowed_extensions=<?= implode(',', $allowed_extensions) ?>",

                // Flash settings
                flash_swf_url: '<?= DIR_CMS_SCRIPTS_URL ?>plupload/Moxie.swf',

                // Silverlight settings
                silverlight_xap_url: '<?= DIR_CMS_SCRIPTS_URL ?>plupload/Moxie.xap',

                init: {
                    PostInit: function () {
                        document.getElementById('filelist').innerHTML = '';

                        document.getElementById('upload_files').onclick = function () {
                            uploader.start();
                            return false;
                        };
                    },

                    FilesAdded: function (up, files) {
                        plupload.each(files, function (file) {
                            document.getElementById('filelist').innerHTML += '<div onclick="filemanager_helpers.removeFile(\''+ file.id +'\')" style="cursor: no-drop" id="' + file.id + '"><var id="deleteFile' + file.id + '">X</var>&nbsp;&nbsp;&nbsp; ' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>';
                            filemanager_helpers.upload_object = up;
                            filemanager_helpers.file_handlers[file.id] = file;
                        });
                    },

                    UploadProgress: function (up, file) {
                        document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span style="float: right; color: #fff; text-align: center; display: inline-block; width: 100px"><span style="background: green; width: '+ file.percent +'%; display: inline-block">' + file.percent + "%</span></span>";
                    },

                    Error: function (up, err) {
                        document.getElementById('console').style.display = 'block';
                        document.getElementById('console').innerHTML += "\nError #" + err.code + ": " + err.message;
                    },

                    UploadComplete: function () { // (up, err)
                        filemanager_helpers.reloadFiles();
                    },

                    BeforeUpload: function () { // (up, err)
                        uploader.setOption('url', "?p=<?= P ?>&do=_upload_multiple&path=<?= $dir ?>&allowed_extensions=<?= implode(',', $allowed_extensions) ?>&exists=" + $('input[name=exists]:checked').val() + '&extract=' + $('input[name=extract]:checked').length + '&cache=<?= NOW ?>');
                    }
                }
            });

            // Helper function for uploader plugin
            var filemanager_helpers = {
                upload_object: null,
                file_handlers: {},
                removeFile: function(file_id) {
                    var file = filemanager_helpers.file_handlers[file_id];
                    filemanager_helpers.upload_object.removeFile(file);
                    $("#" + file_id).remove();
                },
                reloadFiles: function() {
                    $('#file_list_zone').load(filemanager_helpers.current_url + '&files_only');
                    setTimeout(function() {
                        events_on_checkboxes();
                        init_context_events_on_files();
                        ajax_toasters.request_new_messages();
                        filemanager_helpers.reinit_context_menues();
                    }, 100);
                },
                current_url: '<?= SELF ?>',
                loadDirectory: function (link) {
                    // From CKEditor - in separate window
                    if ('<?= (int) isset($_GET['CKEditor']) ?>' == '1') {
                        window.location.href = link.href + '&CKEditor=<?= isset($_GET['CKEditor']) ? $_GET['CKEditor'] : '' ?>&CKEditorFuncNum=<?= isset($_GET['CKEditorFuncNum']) ? $_GET['CKEditorFuncNum'] : '' ?>&langCode=<?= isset($_GET['langCode']) ? $_GET['langCode'] : '' ?>';
                    } else {
                        // Ajax
                        filemanager_helpers.current_url = link.href;
                        $(link).parents('#modal-popup_inner').trigger('popup:load_content', [link.href]);
                    }

                    return false;
                },
                show_create_directory: function() {
                    _.con.open();
                    $('#con_file_create').hide();
                    $('#con_dir_create').show();
                },
                show_create_file: function() {
                    _.con.open();
                    $('#con_dir_create').hide();
                    $('#con_file_create').show();
                },
                delete_files: function(path) {
                    if (!confirm('<?= __('Are you sure?') ?>')) return false;

                    $.get("?p=<?= P ?>&do=_delete&path="+ path, {
                        'path': path
                    }, function () {
                        filemanager_helpers.reloadFiles();
                    });
                },
                reinit_context_menues: function() {
                    // Move all contextMenues to the BODY element - to calculate proper CSS
                    if ($('body > .contextMenu').length < 1) {
                        $('.contextMenu').appendTo(document.body);
                    } else {
                        // Delete new from ajaxed data
                        $('.contextMenu').not('body > .contextMenu').remove();
                    }
                }
            };

            uploader.init();
            filemanager_helpers.reinit_context_menues();
        </script><?php

        if (IS_AJAX_REQUEST) {
            echo ob_get_clean();
            die;
        }
    }

    /**
     * Action for file upload in old browser with support of only one file
     * @deprecated
     */
    public function _upload()
    {
        if (!isset($_FILES['file'], $_POST['path'], $_POST['exists'])) {
            return;
        }

        // Generate path in charge
        $dir =& $_POST['path'];
        $tmp = explode('/', $dir);
        $dir = [];
        foreach ($tmp as $v) {
            if ($v) {
                $dir[] = $v;
            }
        }
        $dir = implode('/', $dir);
        if ($dir) {
            $dir .= '/';
        }

        if ($dir && $dir[0] == '/') {
            $dir = substr($dir, 1);
        }

        // Check uploaded file
        $files = $_FILES['file'];
        if (!$files) {
            return;
        }

        $if_file_exists = $_POST['exists'];

        $so = count($files['name']);

        for ($i = 0; $i < $so; $i++) {
            $f =& $files['name'][$i];
            $f = str_replace(' ', '_', $f);
            if (!preg_match('/^[a-z0-9\_\-\.\,]+$/i', $f)) error('File name "' . $f . '" contains invalid symbols');

            if (!$files['size'][$i]) {
                error('File "' . $dir . $f . '" is NOT uploaded. Probably, file is too big. Please, increase upload limits');
            }

            $src = DIR_BASE . $dir . $f;
            if (!is_uploaded_file($files['tmp_name'][$i])) {
                error('File "' . $dir . $f . '" is NOT uploaded');
            }

            // Behaviour if file already exists on server
            if (file_exists($src)) {
                switch ($if_file_exists) {
                    default:
                    case 'skip':
                        // Skip upload
                        continue;
                        break;
                    case 'overwrite':
                        // Do nothing - file will be overwritten
                        break;
                    case 'rename':
                        // Generate new name
                        $f_name = pathinfo($src, PATHINFO_FILENAME);
                        $f_ext = pathinfo($src, PATHINFO_EXTENSION);

                        $name_iterator = 1;
                        while (is_file($src)) {
                            $src = DIR_BASE . $dir . $f_name . '_' . $name_iterator . '.' . $f_ext;
                            ++$name_iterator;
                        }
                        break;
                }
            }

            if (!@move_uploaded_file($files['tmp_name'][$i], $src)) {
                error('Could not upload file to ' . ($src) . '. Please check directory permissions.');
            }

            App::add('File "' . $dir . $f . '" uploaded');

            // If zip file and checked - extract
            if (isset($_POST['extract']) && class_exists('ZipArchive') && pathinfo($f, PATHINFO_EXTENSION) === 'zip' && file_exists($src)) {
                // Unzip
                $zip = new ZipArchive;
                $zip->open($src);
                $zip->extractTo(DIR_BASE . $dir);
                // Delete archive
                unlink($src);
            }
        }

        Messages::sendGreenAlert('Uploaded');

        back();
    }

    /**
     * Action for Upload files using uploader plugin with multiple files and partial uploads
     */
    public function _upload_multiple()
    {
        $if_file_exists = $_GET['exists'];
        $extract_zips = isset($_GET['extract']) && $_GET['extract'] && class_exists('ZipArchive');
        $allowed_extensions = isset($_GET['allowed_extensions']) ? array_filter(explode(',', $_GET['allowed_extensions'])) : [];

        // Current path in chage
        $dir = $_GET['path'];
        $tmp = explode('/', $dir);
        $dir = [];
        foreach ($tmp as $v) {
            if ($v) {
                $dir[] = $v;
            }
        }
        $dir = implode('/', $dir);
        if ($dir) {
            $dir .= '/';
        }

        if ($dir && $dir[0] == '/') {
            $dir = substr($dir, 1);
        }

        $targetDir = DIR_BASE . $dir;

        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid('file_');
        }

        // Check file is allowed
        if ($allowed_extensions) {
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            if (!in_array($ext, $allowed_extensions)) {
                error('Extension "'. $ext .'" is not allowed');
            }
        }

//        $fileName = strtolower($fileName);
        $fileName = Converter::data2words(strtolower($fileName), ['@', '-', '_', '.']);

        $filePath = $targetDir . $fileName;

        // Behaviour if file already exists on server
        if (file_exists($filePath)) {
            switch ($if_file_exists) {
                default:
                case 'skip':
                    // End upload
                    ob_get_clean();
                    die('1');
                case 'overwrite':
                    // Do nothing - file will be overwritten
                    break;
                case 'rename':
                    // Generate new name
                    $f_name = pathinfo($filePath, PATHINFO_FILENAME);
                    $f_ext = pathinfo($filePath, PATHINFO_EXTENSION);

                    $name_iterator = 1;
                    while (is_file($filePath)) {
                        $filePath = DIR_BASE . $dir . $f_name . '_' . $name_iterator . '.' . $f_ext;
                        ++$name_iterator;
                    }
                    break;
            }
        }

        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;


        // Open temp file
        if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
            error('Failed to open output stream.');
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                error('Failed to move uploaded file.');
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                error('Failed to open input stream.');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                error('Failed to open input stream.');
            }
        }

        // Save file from chunks
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off
            rename("{$filePath}.part", $filePath);

        }

        $extention = pathinfo($filePath, PATHINFO_EXTENSION);
        // Resizeimages and remove EXIF meta
        if (in_array($extention, ['jpg', 'jpeg', 'png', 'gif'])) {
            $image = new Image();
            $image->open($filePath);
            list($width, $height) = getimagesize($filePath);
            if ($width > 4096) { // Maximim is for 4k screens 4096x3072
                $w = 4096;

                $ratio = $width / $w;
                $h = $height / $ratio;

                $image->resize($w, $h);
                $image->save($filePath, $extention, 90);
            }
        }

        // Extract ZIPs
        if ($extract_zips && pathinfo($filePath, PATHINFO_EXTENSION) === 'zip') {
            // Unzip
            $zip = new ZipArchive();
            $zip_open_result = $zip->open($filePath);
            if ($zip_open_result === true) {
                $zip->extractTo(rtrim($targetDir, '/'));
                $zip->close();
                // Delete archive
                unlink($filePath);
            } else {
                dump('ZipArchive failed to open ' . $filePath . '. With error code - ' . $zip_open_result);
            }
        }

        ob_get_clean();
        die('1');
    }

    /**
     * Action for Delete folder or file
     */
    public function _delete()
    {
        $dir =& $_GET['path'];
        if ($dir[0] == '/') $dir = substr($dir, 1);

        App::add('File ' . $dir . ' deleted');

        Messages::sendGreenAlert('File ' . $dir . ' deleted');

        $dir = DIR_BASE . $dir;
        if (is_dir($dir)) {
            FileSystem::remdir($dir);
        } elseif (is_file($dir)) {
            unlink($dir);
        }

        back();
    }

    /**
     * Action for Delete
     */
    public function _multiple_delete()
    {
        if (!$_POST || !isset($_POST['pathes'])) {
            return;
        }

        foreach ($_POST['pathes'] as $v) {
            if (is_file(DIR_BASE . $v)) {
                unlink(DIR_BASE . $v);
            } else {
                FileSystem::remdir(DIR_BASE . $v);
            }
        }
    }

    /**
     * Action for Copy recursively multiple items
     */
    public function _multiple_copy()
    {
        if (!$_REQUEST || !isset($_REQUEST['pathes'], $_REQUEST['current_path'])) {
            return;
        }

        $paths = explode(',', $_REQUEST['pathes']);

        $current_dir = DIR_BASE . $_REQUEST['current_path'];

        $items_to_copy = [];
        foreach ($paths as $v) {
            // Cut leading slash
            if (substr($v, 0, 1) == '/') {
                $v = substr($v, 1);
            }
            $items_to_copy[] = $v;
        }

        // Copy items to current folder
        foreach ($items_to_copy as $v) {
            $full_path_to = $current_dir . basename($v);
            if (file_exists($full_path_to)) {
                $i = 1;
                while (file_exists($full_path_to . '.' . $i)) {
                    ++$i;
                }
                $full_path_to = $full_path_to . '.' . $i;
            }


            $full_path_from = DIR_BASE . $v;

            if (is_dir($full_path_from)) {
                $full_path_to .= '/';
            }

            FileSystem::copyRecursive($full_path_from, $full_path_to);

            App::add('Copied directory "'. $full_path_from .'" to "'. $full_path_to .'');

            Messages::sendGreenAlert('Copied directory "'. $full_path_from .'" to "'. $full_path_to .'');
        }

        exit('1');
    }

    /**
     * Action for Downloading files as ZIP file
     */
    public function _multiple_download()
    {
        ob_get_clean();

        if (!$_REQUEST || !isset($_REQUEST['pathes'])) {
            return;
        }

        $paths = $_REQUEST['pathes'];

        $items_to_add = [];
        foreach ($paths as $v) {
            // Cut leading slash
            if (substr($v, 0, 1) == '/') {
                $v = substr($v, 1);
            }
            $items_to_add[] = $v;
        }

        FileSystem::mkDir(DIR_TEMP);

        $zip_path = DIR_TEMP_URL . UID::uid10() . '.zip';

        $zip = new ZipArchive;
        $zip->open(DIR_BASE . $zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Add items to current archive
        foreach ($items_to_add as $v) {
            $full_path_from = DIR_BASE . $v;
            if (is_file($full_path_from)) {
                $zip->addFile($full_path_from, basename($v));
            } elseif (is_dir($full_path_from)) {
                // Iterate all files in folder
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($full_path_from),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file)
                {
                    // Skip directories (they would be added automatically)
                    if (!$file->isDir())
                    {
                        // Get real and relative path for current file
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($full_path_from) + 1);

                        // Add current file to archive
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }
        }

        $zip->close();

        App::add('Downloaded files as .zip file');

        Messages::sendGreenAlert('Downloaded files as .zip file');

        exit('' . $zip_path);
    }

    /**
     * Edit contents and name of file
     */
    public function edit()
    {
        $dir =& $_GET['path'];
        if ($dir[0] == '/') {
            $dir = substr($dir, 1);
        }
        $dir_base = DIR_BASE . $dir;
        $ext = pathinfo($dir, PATHINFO_EXTENSION);

        $ext_allowed_for_edit = ['txt', 'html', 'php', 'js', 'htaccess', 'css', 'xml', ''];
        if (!in_array($ext, $ext_allowed_for_edit)) {
            error('File content can not be edited');
        }


        echo CmsForm::getInstance()
            ->setAction('?p=' . P . '&do=_edit_content&path=' . $dir)
            ->setSubmitButton(new CmsButton('Update'))
            ->addField('Name', CmsHTML::getInstance('')
                ->setValue(pathinfo($dir, PATHINFO_BASENAME))
            )
            ->addField('Name hidden', CmsInputHidden::getInstance('name')
                ->setValue(pathinfo($dir, PATHINFO_BASENAME))
            )
            ->addField('Content', CmsTextarea::getInstance('content')
                ->setValue(htmlspecialchars(file_get_contents($dir_base), ENT_COMPAT, 'utf-8'))
                ->setRowCount(30)
            )
        ;
    }

    /**
     * Action for Edit file or folder
     */
    public function _edit()
    {
        $dir =& $_GET['path'];
        if (isset($dir[0]) && $dir[0] == '/') {
            $dir = substr($dir, 1);
        }

        $type = isset($_POST['type_of_1']) ? $_POST['type_of_1'] : (isset($_POST['type_of_2']) ? $_POST['type_of_2'] : (isset($_POST['type_of_3']) ? $_POST['type_of_3'] : false));

        if ($type == 'file') { // Files
            switch ($_GET['action']) {
                case 'rename':
                    if ($_POST['current_path'][0] == '/') $_POST['current_path'] = substr($_POST['current_path'], 1);
                    $new_path = DIR_BASE . $dir . $_POST['new_name'];
                    if (file_exists($new_path)) error('File "' . htmlspecialchars($_POST['new_name'], ENT_QUOTES) . '" already exists');
                    rename(DIR_BASE . $_POST['current_path'], $new_path);

                    App::add('File "' . $_POST['current_path'] . '" renamed to "' . $new_path . '"');

                    Messages::sendGreenAlert('File "' . $_POST['current_path'] . '" renamed to "' . $new_path . '"');

                    break;
                case 'delete':
                    if ($_POST['remove_path'][0] == '/') $_POST['remove_path'] = substr($_POST['remove_path'], 1);
                    if (is_file(DIR_BASE . $_POST['remove_path'])) unlink(DIR_BASE . $_POST['remove_path']);

                    App::add('File "' . $_POST['remove_path'] . '" deleted');

                    Messages::sendGreenAlert('File "' . $_POST['remove_path'] . '" deleted');

                    break;
                case 'content':
                    if ($_POST['file_name'][0] == '/') $_POST['file_name'] = substr($_POST['file_name'], 1);
                    file_put_contents(DIR_BASE . $_POST['file_name'], $_POST['content']);

                    App::add('Content of file "' . $_POST['file_name'] . '" edited');

                    Messages::sendGreenAlert('Content of file "' . $_POST['file_name'] . '" edited');

                    break;
            }
        } elseif ($type == 'folder') { // folders
            switch ($_GET['action']) {
                case 'rename':
                    if ($_POST['current_path'][0] == '/') $_POST['current_path'] = substr($_POST['current_path'], 1);
                    $new_path = DIR_BASE . $dir . $_POST['new_name'];
                    if (file_exists($new_path)) error('Folder "' . htmlspecialchars($_POST['new_name'], ENT_QUOTES) . '" already exists');
                    rename(DIR_BASE . $_POST['current_path'], $new_path);

                    App::add('Folder "' . $_POST['current_path'] . '" renamed to "' . $new_path . '"');

                    Messages::sendGreenAlert('Folder "' . $_POST['current_path'] . '" renamed to "' . $new_path);

                    break;
                case 'delete':
                    if ($_POST['remove_path'][0] == '/') $_POST['remove_path'] = substr($_POST['remove_path'], 1);
                    FileSystem::remDir(DIR_BASE . $_POST['remove_path'], $_POST['leave_folder']);

                    App::add('Folder "' . $_POST['remove_path'] . '" deleted');

                    Messages::sendGreenAlert('Folder "' . $_POST['remove_path'] . '" deleted');

                    break;
            }
        } elseif (!$type) { // New file or folder
            switch ($_GET['action']) {
                case 'dircreate':
                    if (!isset($_POST['dirname']) || !FileSystem::checkFileName($_POST['dirname'])) return;

                    $new_path = DIR_BASE . $dir . $_POST['dirname'];
                    if (file_exists($new_path)) error('Folder "' . htmlspecialchars($_POST['dirname'], ENT_QUOTES) . '" already exists');

                    FileSystem::mkDir($new_path);

                    App::add('Folder "' . DIR_BASE_URL . $dir . $_POST['dirname'] . '" created');

                    Messages::sendGreenAlert('Folder "' . DIR_BASE_URL . $dir . $_POST['dirname'] . '" created');

                    break;
                case 'filecreate':
                    if (!isset($_POST['file_name'], $_POST['content']) || !FileSystem::checkFileName($_POST['file_name'])) return;

                    $new_path = DIR_BASE . $dir . $_POST['file_name'];
                    if (file_exists($new_path)) error('File "' . htmlspecialchars($_POST['file_name'], ENT_QUOTES) . '" already exists');

                    FileSystem::mkDir(DIR_BASE . $dir);
                    file_put_contents($new_path, $_POST['content']);

                    App::add('File "' . $new_path . '" created');

                    Messages::sendGreenAlert('File "' . $new_path . '" created');

                    break;
            }
        }

        back();
    }

    /**
     * Create Directory
     */
    public function create_directory()
    {
        $dir =& $_GET['path'];
        if ($dir[0] == '/') {
            $dir = substr($dir, 1);
        }

        echo CmsForm::getInstance()
            ->setAction('?p=' . P . '&do=_create_directory&path=' . $dir)
            ->setSubmitButton(new CmsButton('Create'))
            ->addField('Path', CmsHTML::getInstance('path')
                ->setValue($dir)
            )
            ->addField('Directory name', CmsInputText::getInstance('name'));
    }

    /**
     * Action for Create directory
     */
    public function _create_directory()
    {
        $dir = $_GET['path'];
        if ($dir && $dir[0] == '/') $dir = substr($dir, 1);

        FileSystem::mkDir(DIR_BASE . $dir . $_POST['name']);

        App::add('Diretory "' . $dir . $_POST['name'] . '" created');

        Messages::sendGreenAlert('Diretory "' . $dir . $_POST['name'] . '" created');

        go('?p=' . P . '&do=show_files&nomenu&path=' . $dir);
    }

    /**
     * Create file
     */
    public function create_file()
    {
        $dir = $_GET['path'];
        if ($dir[0] == '/') $dir = substr($dir, 1);

        echo CmsForm::getInstance()
            ->setAction('?p=' . P . '&do=_create_file&path=' . $dir)
            ->setSubmitButton(new CmsButton('Create'))
            ->addField('Path', CmsHTML::getInstance('path')
                ->setValue($dir)
            )
            ->addField('File name', CmsInputText::getInstance('name')
                ->hint('With extension')
            )
            ->addField('File content', CmsTextarea::getInstance('content')
                ->setRowCount(30)
            );
    }

    /**
     * Action for Create file
     */
    public function _create_file()
    {
        $dir =& $_GET['path'];
        if ($dir[0] == '/') $dir = substr($dir, 1);

        if (!file_exists(DIR_BASE . $dir . $_POST['name'])) file_put_contents(DIR_BASE . $dir . $_POST['name'], $_POST['content']);

        App::add('File "' . $dir . $_POST['name'] . '" created');

        Messages::sendGreenAlert('File "' . $dir . $_POST['name'] . '" created');

        go('?p=' . P . '&do=show_files&nomenu&path=' . $dir);
    }

    /**
     * Open file, get it's content
     */
    public function _get_file_content()
    {
        ob_clean();
        echo file_get_contents(DIR_BASE . $_GET['path']);
        die;
    }

    /**
     * Action for Edit contents
     */
    public function _edit_content()
    {
        $dir =& $_GET['path'];
        if (isset($dir[0]) && $dir[0] == '/') $dir = substr($dir, 1);

        if (!is_file(DIR_BASE . $dir)) error('Not a file');

        file_put_contents(DIR_BASE . $dir, $_POST['content']);

        App::add('Content of file "' . $_POST['name'] . '" edited');

        Messages::sendGreenAlert('Content of file "' . $_POST['name'] . '" edited');

        $path_to_dir = explode('/', $dir);
        array_pop($path_to_dir);

        go('?p=' . P . '&do=show_files&nomenu&path=' . implode('/', $path_to_dir) . '/');
    }
}