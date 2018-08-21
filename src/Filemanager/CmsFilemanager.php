<?php

namespace TMCms\Admin\Filemanager;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use TMCms\Admin\Filemanager\Entity\FilePropertyEntity;
use TMCms\Admin\Filemanager\Entity\FilePropertyEntityRepository;
use TMCms\Admin\Messages;
use TMCms\Admin\Users;
use TMCms\Files\FileSystem;
use TMCms\Files\Image;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\HTML\Cms\Element\CmsButton;
use TMCms\HTML\Cms\Element\CmsCheckbox;
use TMCms\HTML\Cms\Element\CmsHtml;
use TMCms\HTML\Cms\Element\CmsInputHidden;
use TMCms\HTML\Cms\Element\CmsInputText;
use TMCms\HTML\Cms\Element\CmsRadioBox;
use TMCms\HTML\Cms\Element\CmsTextarea;
use TMCms\HTML\Cms\Filter\Hidden;
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

        $for_reload = isset($_GET['for_reload']);

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
        // To make it work on Windows;
        $dir = str_replace('\\', '/', $dir);
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

        // Forms
        ?>
        <div id="con_container" style="padding: 10px; border: 1px solid green; margin: 10px; display: none"></div>
        <a href="" onclick="_.con.close(); return false;" id="con_container_close"
           style="position: absolute; top: 15px; right: 20px; z-index: 15; display: none">X</a>
        <?php

        // Show top bar if we are allowed to view folders
        if (!$files_only && !$for_reload):
            ?>
            <!--suppress JSUnresolvedFunction -->
            <div style="padding: 10px; position: relative">
                <a href="" onclick="filemanager_helpers.show_create_directory(); return false;">Create folder</a>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <a href="?p=<?= P ?>&do=filemanager&nomenu&path=<?= $path_up ?>" onclick="filemanager_helpers.loadDirectory(this); return false;">Go up</a>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <a class="fa fa-repeat" onclick="filemanager_helpers.reloadFiles(); return false;"></a>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                Current path: /<?= implode('/', $path_links) ?>
                <hr>
                <a onclick="filemanager_helpers.show_create_file(); return false;"
                   href="?p=<?= P ?>&do=create_file&nomenu&path=<?= $dir ?>">Create file</a>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                Filter by name&nbsp;&nbsp;<input type="text" id="filter_name" placeholder="File or folder name">
                <hr>
                <span id="multiple_commands">
                    <a href="" onclick="multiple.download(); return false"><?= __('Download') ?></a>
                    &nbsp;&nbsp;
                    <a href=""
                       onclick="if (confirm('<?= __('Are you sure?') ?>')) {multiple.delete_files()}; return false;"><?= __('Delete') ?></a>
                    &nbsp;&nbsp;
                    <a href="" onclick="multiple.copy(this)"><?= __('Copy') ?></a>
                    &nbsp;&nbsp;
                    <a href="" id="multiple_paste" style="display:none;"
                       onclick="multiple.paste(this)"><?= __('Paste') ?></a>
                    <hr>
                </span>
                <div style="position: fixed; top: 100px; right: 100px; width: 25%; z-index: 10; background: #bbb; border: 1px solid #bbb;">
                    <img width="100%" id="filemanager_current_image" style="display: none" src="<?= DIR_CMS_IMAGES_URL ?>_.gif">
                </div>
            </div>
        <?php endif; ?>

        <div style="min-height: 350px; overflow-y: auto; padding: 10px; border-top: 2px solid #000;"
             id="file_list_zone">
            <table cellspacing="0" cellpadding="0" style="line-height:20px">
                <?php if (!$files_only || $for_reload): ?>
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
                                <a oncontextmenu="context_menus.folders(this); return false;" id="folder_<?= $k ?>" class="dir_context" href="?p=<?= P ?>&do=filemanager&nomenu&path=<?= $v ?>" onclick="return setSelectedToInput(this);" data-path="<?= $v ?>" ondblclick="filemanager_helpers.loadDirectory(this); return false;" data-name="<?= basename($v) ?>"><?= basename($v) ?></a>
                            </td>
                            <td></td>
                            <td></td>
                            <td align="center">
                                <a href="?p=<?= P ?>&do=_delete&path=<?= $v ?>" onclick="if (confirm('<?= __('Are you sure?') ?>')) filemanager_helpers.delete_files('<?= $v ?>'); return false;">x</a>
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
                            <a oncontextmenu="context_menus.files(this); return false;" id="file_<?= /*$k*/ preg_replace('~[/\.\s]~', '-', $v) ?>"
                               class="file_context<?= $type_by_extension ?>" href=""
                               onclick="return setSelectedToInput(this);" data-path="<?= $v ?>" data-url="<?= BASE_URL.$v ?>"  ondblclick="done();"
                                <?php if ($type_by_extension == '_img'): ?>
                                    onmouseover="$('#filemanager_current_image').attr('src', '<?= $v ?>').show()"
                                    onmouseout="$('#filemanager_current_image').attr('src', '<?= DIR_CMS_IMAGES_URL ?>_.gif').hide()"
                                <?php endif; ?>

                                <?php if ($type_by_extension == '_text'): ?>
                                    data-text-editable="1"
                                <?php endif; ?>
                               data-name="<?= basename($v) ?>"><?= basename($v) ?></a>
                        </td>
                        <td></td>
                        <td><?= Converter::formatDataSizeFromBytes(filesize(DIR_BASE . $v)) ?></td>
                        <td align="center">
                            <a href="?p=<?= P ?>&do=_delete&path=<?= $v ?>" onclick="if (confirm('<?= __('Are you sure?') ?>')) filemanager_helpers.delete_files('<?= $v ?>'); return false;">x</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>


            <?php if ($files_only || $for_reload) {
                // Stop further rendering if we can locked to see only files
                echo ob_get_clean();
                die;
            } ?>
        </div>
        <br>
        <div style="margin: auto 5px; border-bottom: 2px solid #000;">
        <?php
        // Simple upload form
        echo CmsForm::getInstance()
            ->disableFullView()
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
        $max_upload_file = $max_post = Converter::formatIniParamSize(ini_get('post_max_size'));
        $max_upload = Converter::formatIniParamSize(ini_get('upload_max_filesize'));
        if ($max_upload < $max_upload_file) {
            $max_upload_file = $max_upload;
        }

        // Modern upload form with multiple file selects and large file uploads
        $upload_form = CmsForm::getInstance()
            ->disableFullView()
            ->setButtonSubmit(CmsButton::getInstance('Upload')
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
            <a id="pickfiles" href="javascript:;" style="display: block; letter-spacing: 3px; padding-top: 15px; height: 50px; font-size: 13px; text-align: center; border: 1px solid black">Click to select files, or drag files here</a>
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
                $('a[data-path="' + link.getAttribute('data-path') + '"]')
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
            }

            // Main Container
            var _ = {
                con: {
                    close: function () {
                        $('#con_container').hide().html(' ');
                        $('#con_container_close').hide();
                    },
                    open: function () {
                        $('#con_container').show();
                        $('#con_container_close').show();
                    },
                    request_view: function (path) {
                        $.ajax({
                            url: path + '&ajax&cache=' + Date.now(),
                            type: 'GET',
                            success: function (data) {
                                $('#con_container').html(data);
                                $('#button_to_not_ajaxify').hide();
                                filemanager_helpers.reloadFiles();
                            }
                        });
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
                    $('a[data-name]').closest('tr').show();
                } else {
                    $('a[data-name]').closest('tr').hide();
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

                    $.post('?p=filemanager&nomenu&do=_multiple_delete&ajax&cache=' + Date.now(),
                        {pathes: items},
                        function () {
                            filemanager_helpers.reloadFiles();
                        });
                },
                // Delete files from server
                download: function () {
                    var items = this.get_selected_item_sources();

                    $.ajax({
                        url: '?p=filemanager&nomenu&do=_multiple_download&ajax&cache=' + Date.now(),
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            pathes: items
                        },
                        success: function (data) {
                            // Show error
                            if (data.result != 1) {
                                toastr.error(data.message);
                                return;
                            }
                            // Download file
                            location.href = data.zip_path;
                        }
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
                        $.get('?p=filemanager&nomenu&do=_multiple_copy&ajax&cache=' + Date.now(),
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
                current_url: '?<?= QUERY ?>',
                removeFile: function(file_id) {
                    var file = filemanager_helpers.file_handlers[file_id];
                    filemanager_helpers.upload_object.removeFile(file);
                    $("#" + file_id).remove();
                },
                reloadFiles: function() {
                    $('#file_list_zone').load(filemanager_helpers.current_url + '&for_reload');
                    setTimeout(function() {
                        events_on_checkboxes();
                        ajax_toasters.request_new_messages();
                    }, 100);
                },
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
                editDirectoryName: function (directory_path) {
                    _.con.close();
                    _.con.open();
                    _.con.request_view('?p=<?= P ?>&do=edit_directory&nomenu&path=' + directory_path);
                },
                show_create_directory: function() {
                    _.con.close();
                    _.con.open();
                    _.con.request_view('?p=<?= P ?>&do=create_directory&nomenu&path=<?= $dir ?>');
                },
                show_create_file: function() {
                    _.con.open();
                    $('#con_dir_create').hide();
                    $('#con_file_create').show();
                    _.con.request_view('?p=<?= P ?>&do=create_file&nomenu&path=<?= $dir ?>');
                },
                delete_files: function(path) {
                    $.get("?p=<?= P ?>&do=_delete&path="+ path, {
                        'path': path
                    }, function () {
                        filemanager_helpers.reloadFiles();
                    });
                },
                editFileName: function (file_path) {
                    _.con.close();
                    _.con.open();
                    _.con.request_view('?p=<?= P ?>&do=edit_file&nomenu&path=' + file_path);
                },
                editFileContent: function (file_path) {
                    _.con.close();
                    _.con.open();
                    _.con.request_view('?p=<?= P ?>&do=edit_file_content&nomenu&path=' + file_path);
                },
                editMetaData: function (file_path) {
                    _.con.close();
                    _.con.open();
                    _.con.request_view('?p=<?= P ?>&do=edit_meta_data&nomenu&path=' + file_path);
                },
                copyFileUrl: function (el) {
                    var url = $(el).data('url');
                    var trigger = $('<span data-clipboard-text="'+url+'"></span>');
                    var clipboard = new ClipboardJS(trigger[0]);
                    clipboard.on('success', function(e) {
                        console.info(e.text);
                        toastr.success(e.text+'<br/> is copied to clipboard');
                        clipboard.destroy();
                        trigger.remove();
                    });
                    clipboard.on('error', function(e) {
                        console.error(e.text);
                        toastr.error(e.text+'<br/> is not copied.');
                        clipboard.destroy();
                        trigger.remove();
                    });
                    trigger.click();

                }
            };

            var context_menus = {
                folders: function(el) {
                    var $el = $(el);
                    var items = {
                        0: {
                            'name': 'Open folder',
                            'href': '',
                            'confirm': 0,
                            'popup': 0,
                            'js': function () {
                                filemanager_helpers.loadDirectory(el);
                            }
                        },
                        2: {
                            'name': 'Edit folder name',
                            'href': '',
                            'confirm': 0,
                            'popup': 0,
                            'js': function () {
                                filemanager_helpers.editDirectoryName($el.data('path'));
                            }
                        },
                        4: {
                            'name': 'Delete folder',
                            'href': '',
                            'confirm': 1,
                            'popup': 0,
                            'js': function() {
                                filemanager_helpers.delete_files($el.data('path'));
                            }
                        },
                        5: {
                            'name': 'Edit folder properties',
                            'href': '',
                            'confirm': 0,
                            'popup': 0,
                            'js': function () {
                                filemanager_helpers.editMetaData($el.data('path'));
                            }
                        }
                    };

                    $.contextMenu({
                        selector: '#' + $el.attr('id'),
                        callback: function(key, options) {
                            var params = options.items[key];
                            if (typeof params.confirm != 'undefined' && params.confirm) {
                                if (!confirm('<?= __('Are you sure?') ?>')) {
                                    return false;
                                }
                            }

                            if (typeof params.href != 'undefined' && params.href) {
                                if (typeof params.popup != 'undefined' && params.popup) {
                                    window.open(params.href);
                                } else {
                                    alert(params.href);
                                    window.location = params.href;
                                }
                            }

                            if (typeof params.js != 'undefined' && params.js) {
                                params.js();
                            }
                        },
                        items: items
                    });

                    return false;
                },
                files: function(el) {
                    var $el = $(el);
                    var items = {
                        0: {
                            'name': 'Download file',
                            'href': $el.data('path'),
                            'confirm': 0,
                            'popup': 1
                        },
                        2: {
                            'name': 'Edit file name',
                            'href': '',
                            'confirm': 0,
                            'popup': 0,
                            'js': function () {
                                filemanager_helpers.editFileName($el.data('path'));
                            }
                        },
                        4: {
                            'name': 'Delete file',
                            'href': '',
                            'confirm': 1,
                            'popup': 0,
                            'js': function () {
                                filemanager_helpers.delete_files($el.data('path'));
                            }
                        },
                        5: {
                            'name': 'Edit file properties',
                            'href': '',
                            'confirm': 0,
                            'popup': 0,
                            'js': function () {
                                filemanager_helpers.editMetaData($el.data('path'));
                            }
                        },
                        6: {
                            'name': 'Copy link to file',
                            'href': '',
                            'confirm': 0,
                            'popup': 0,
                            'js': function () {
                                filemanager_helpers.copyFileUrl($el);
                            }
                        }
                    };

                    if ($el.data('text-editable') == "1") {
                        items[3] = {
                            'name': 'Edit file content',
                            'href': '',
                            'confirm': 0,
                            'popup': 0,
                            'js': function () {
                                filemanager_helpers.editFileContent($el.data('path'));
                            }
                        };
                    }

                    $.contextMenu({
                        selector: '#' + $el.attr('id'),
                        callback: function (key, options) {
                            var params = options.items[key];
                            if (typeof params.confirm != 'undefined' && params.confirm) {
                                if (!confirm('<?= __('Are you sure?') ?>')) {
                                    return false;
                                }
                            }

                            if (typeof params.href != 'undefined' && params.href) {
                                if (typeof params.popup != 'undefined' && params.popup) {
                                    window.open(params.href);
                                } else {
                                    alert(params.href);
                                    window.location = params.href;
                                }
                            }

                            if (typeof params.js != 'undefined' && params.js) {
                                params.js();
                            }
                        },
                        items: items
                    });

                    return false;
                }
            };

            uploader.init();
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
        $fileName = Converter::removeOddFileNameSymbols(strtolower($fileName), ['@', '-', '_', '.']);

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

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        // Resize images and remove EXIF meta
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $image = new Image();
            $image->open($filePath);
            list($width, $height) = getimagesize($filePath);
            if ($width > 4096) { // Maximum is for 4k screens 4096x3072
                $w = 4096;

                $ratio = $width / $w;
                $h = $height / $ratio;

                $w = (int)$w;
                $h = (int)$h;
                $image->resize($w, $h);
                $image->save($filePath, $extension, 90);
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

        // Delete all file properties
        /** @var FilePropertyEntity $properties */
        $properties = new FilePropertyEntityRepository;
        $properties->setWherePath($dir);
        $properties->deleteObjectCollection();

        $dir = DIR_BASE . $dir;
        if (is_dir($dir)) {
            FileSystem::remdir($dir);
        } elseif (is_file($dir)) {
            unlink($dir);
        }

        App::add('File ' . $dir . ' deleted');
        Messages::sendGreenAlert('File ' . $dir . ' deleted');

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
            // Delete all properties
            /** @var FilePropertyEntity $properties */
            $properties = new FilePropertyEntityRepository;
            $properties->setWherePath($v);
            $properties->deleteObjectCollection();

            if (is_file(DIR_BASE . $v)) {
                unlink(DIR_BASE . $v);
            } else {
                FileSystem::remdir(DIR_BASE . $v);
            }
        }

        App::add('Deleted multiple files');
        Messages::sendGreenAlert('Deleted');

        exit('1');
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

        $res = [
            'result' => 0,
            'message' => '',
            'zip_path' => '',
        ];

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

        $zip_path = DIR_TEMP_URL . NOW . '_' . UID::uid10() . '.zip';

        $zip = new ZipArchive;
        $zip->open(DIR_BASE . $zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $res['zip_path'] = $zip_path;

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

        try {
            $zip->close();
        } catch (\Exception $e) {
            $res['message'] = $e->getMessage();
        }

        $res['result'] = 1;

        App::add('Downloaded files as .zip file');

        Messages::sendGreenAlert('Downloaded files as .zip file');

        echo json_encode($res, JSON_OBJECT_AS_ARRAY);

        exit;
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

        echo CmsFormHelper::outputForm(NULL, [
            'action' => '?p=' . P . '&do=_create_directory&path=' . $dir,
            'ajax' => true,
            'ajax_callback' => 'filemanager_helpers.reloadFiles(); _.con.close();',
            'full' => false,
            'fields' => [
                'path' => [
                    'type' => 'hidden',
                    'value' => $dir
                ],
                'name' => [
                    'title' => __('Folder name'),
                ],
            ],
            'button' => __('Create Folder'),
        ]);

        if (IS_AJAX_REQUEST) {
            die;
        }
    }

    public function _create_directory()
    {
        $folder = DIR_BASE . $_POST['path'] . $_POST['name'];

        if (!FileSystem::checkFileName($_POST['name'])) {
            Messages::sendRedAlert('Wrong folder name');

            if (IS_AJAX_REQUEST) {
                die;
            }
        }

        // Check exists
        if (file_exists($folder)) {
            Messages::sendRedAlert('Folder "' . $_POST['name'] . '" already exists');

            if (IS_AJAX_REQUEST) {
                die;
            }
        }

        // Create new
        FileSystem::mkDir(DIR_BASE . $_POST['path'] . $_POST['name']);
        App::add('Folder "' . $_POST['name'] . '" created');
        Messages::sendGreenAlert('Folder "' . $_POST['name'] . '" created');

        if (IS_AJAX_REQUEST) {
            die;
        }
    }

    /**
     * Edit Directory name
     */
    public function edit_directory()
    {
        $dir =& $_GET['path'];
        if ($dir[0] == '/') {
            $dir = substr($dir, 1);
        }

        echo CmsFormHelper::outputForm(NULL, [
            'action' => '?p=' . P . '&do=_edit_directory&path=' . $dir,
            'ajax' => true,
            'ajax_callback' => 'filemanager_helpers.reloadFiles(); _.con.close();',
            'full' => false,
            'fields' => [
                'original' => [
                    'type' => 'hidden',
                    'value' => $dir
                ],
                'new' => [
                    'title' => __('Directory name'),
                    'value' => basename($dir),
                ],
            ],
            'button' => __('Rename Folder'),
        ]);

        if (IS_AJAX_REQUEST) {
            die;
        }
    }

    public function _edit_directory()
    {
        if (!FileSystem::checkFileName($_POST['new'])) {
            Messages::sendRedAlert('Wrong folder name');

            if (IS_AJAX_REQUEST) {
                die;
            }
        }

        $original = DIR_BASE . $_POST['original'];

        // Check exists base
        if (!file_exists($original)) {
            Messages::sendRedAlert('Base folder "' . $_POST['original'] . '" not found');

            if (IS_AJAX_REQUEST) {
                die;
            }
        }

        // Check exists target
        $tmp = array_filter(explode(DIRECTORY_SEPARATOR, $original));
        // Change the last element of path
        $keys = array_keys($tmp);
        $last_key = end($keys);
        $tmp[$last_key] = $_POST['new'];
        $new_path = '/' . implode('/', $tmp) . '/';

        if (file_exists($new_path)) {
            Messages::sendRedAlert('Folder "' . $_POST['new'] . '" exists');

            if (IS_AJAX_REQUEST) {
                die;
            }
        }

        // Rename folder
        rename($original, $new_path);
        App::add('Folder renamed to "' . $_POST['new'] . '"');
        Messages::sendGreenAlert('Folder renamed to "' . $_POST['new'] . '"');

        if (IS_AJAX_REQUEST) {
            die;
        }
    }

    /**
     * Create file
     */
    public function create_file()
    {
        $dir =& $_GET['path'];
        if ($dir[0] == '/') {
            $dir = substr($dir, 1);
        }

        echo CmsFormHelper::outputForm(NULL, [
            'action' => '?p=' . P . '&do=_create_file&path=' . $dir,
            'ajax' => true,
            'ajax_callback' => 'filemanager_helpers.reloadFiles(); _.con.close();',
            'full' => false,
            'fields' => [
                'path' => [
                    'type' => 'hidden',
                    'value' => $dir
                ],
                'name' => [
                    'title' => __('File name'),
                ],
            ],
            'button' => __('Create File'),
        ]);

        if (IS_AJAX_REQUEST) {
            die;
        }
    }

    /**
     * Action for Create file
     */
    public function _create_file()
    {
        $dir =& $_GET['path'];
        if ($dir[0] == '/') {
            $dir = substr($dir, 1);
        }

        if (!FileSystem::checkFileName($_POST['name'])) {
            Messages::sendRedAlert('Wrong file name');

            if (IS_AJAX_REQUEST) {
                die;
            }
        }

        if (file_exists(DIR_BASE . $dir . $_POST['name'])) {
            Messages::sendRedAlert('File "' . $_POST['name'] . '" exists');

            if (IS_AJAX_REQUEST) {
                die;
            }
        }

        file_put_contents(DIR_BASE . $dir . $_POST['name'], '');
        App::add('File "' . $dir . $_POST['name'] . '" created');
        Messages::sendGreenAlert('File "' . $dir . $_POST['name'] . '" created');

        if (IS_AJAX_REQUEST) {
            die;
        }
    }

    /**
     * Edit file name
     */
    public function edit_file()
    {
        $dir =& $_GET['path'];
        if ($dir[0] == '/') {
            $dir = substr($dir, 1);
        }

        echo CmsFormHelper::outputForm(NULL, [
            'action' => '?p=' . P . '&do=_edit_file&path=' . $dir,
            'ajax' => true,
            'ajax_callback' => 'filemanager_helpers.reloadFiles(); _.con.close();',
            'full' => false,
            'fields' => [
                'original' => [
                    'type' => 'hidden',
                    'value' => $dir
                ],
                'new' => [
                    'title' => __('File name'),
                    'value' => basename($dir),
                ],
            ],
            'button' => __('Rename File'),
        ]);

        if (IS_AJAX_REQUEST) {
            die;
        }
    }

    public function _edit_file()
    {
        if (!FileSystem::checkFileName($_POST['new'])) {
            Messages::sendRedAlert('Wrong file name');

            if (IS_AJAX_REQUEST) {
                die;
            }
        }

        $original = DIR_BASE . $_POST['original'];

        // Check exists base
        if (!file_exists($original)) {
            Messages::sendRedAlert('Base file "' . $_POST['original'] . '" not found');

            if (IS_AJAX_REQUEST) {
                die;
            }
        }

        // Check exists target
        $tmp = array_filter(explode(DIRECTORY_SEPARATOR, $original));
        // Change the last element of path
        $keys = array_keys($tmp);
        $last_key = end($keys);
        $tmp[$last_key] = $_POST['new'];
        $new_path = '/' . implode('/', $tmp);

        if (file_exists($new_path)) {
            Messages::sendRedAlert('File "' . $_POST['new'] . '" exists');

            if (IS_AJAX_REQUEST) {
                die;
            }
        }

        // Rename folder
        rename($original, $new_path);
        App::add('File renamed to "' . $_POST['new'] . '"');
        Messages::sendGreenAlert('File renamed to "' . $_POST['new'] . '"');

        if (IS_AJAX_REQUEST) {
            die;
        }
    }

    /**
     * Edit file name
     */
    public function edit_meta_data()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _edit_meta_data()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    /**
     * Open file, get it's content
     */
    public function edit_file_content()
    {
        $path =& $_GET['path'];
        if ($path[0] == '/') {
            $path = substr($path, 1);
        }

        echo CmsFormHelper::outputForm(NULL, [
            'action' => '?p=' . P . '&do=_edit_file_content&path=' . $path,
            'ajax' => true,
            'ajax_callback' => '_.con.close();',
            'full' => false,
            'fields' => [
                'name' => [
                    'type' => 'html',
                    'value' => $path
                ],
                'path' => [
                    'type' => 'hidden',
                    'value' => $path
                ],
                'content' => [
                    'type' => 'textarea',
                    'rows' => 10,
                    'title' => __('File content'),
                    'value' => file_get_contents(DIR_BASE . $path),
                ],
            ],
            'button' => __('Update File Content'),
        ]);

        if (IS_AJAX_REQUEST) {
            die;
        }
    }

    /**
     * Action for Edit contents
     */
    public function _edit_file_content()
    {
        $path = DIR_BASE . $_POST['path'];

        // Check exists base
        if (!file_exists($path)) {
            Messages::sendRedAlert('Base file "' . $_POST['path'] . '" not found');

            if (IS_AJAX_REQUEST) {
                die;
            }
        }

        file_put_contents($path, $_POST['content']);
        App::add('Content of file "' . $_POST['path'] . '" edited');
        Messages::sendGreenAlert('File content updated');

        if (IS_AJAX_REQUEST) {
            die;
        }
    }
}
