<?php

namespace TMCms\Admin\Tools;

use TMCms\Admin\Menu;
use TMCms\Cache\Cacher;
use TMCms\Config\Constants;
use TMCms\Config\Entity\SettingEntity;
use TMCms\Config\Entity\SettingEntityRepository;
use TMCms\Config\Settings;
use TMCms\DB\SQL;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\Element\CmsHtml;
use TMCms\Modules\IModule;
use TMCms\Traits\singletonInstanceTrait;

defined('INC') or exit;

class CmsTools implements IModule
{
    use singletonInstanceTrait;

    public function _default()
    {
        Menu::getInstance()
            ->addHelpText('Site based settings');

        BreadCrumbs::getInstance()
            ->addCrumb('Common');

        // Ensure db exists
        new SettingEntityRepository();

        $settings = Settings::getInstance()->init(true);

        // Geo IP last update time
        $last_geo_ip_ts = Cacher::getInstance()->getDefaultCacher()->get('geo_ip_lmt');
        if (!$last_geo_ip_ts) {
            $last_geo_ip_ts = 0;
        }

        // Check Geo IP is loaded
        if (!SQL::getInstance()->tableExists('cms_maxmind_geoip_c')) {
            $geo_ip_status = '<span style="color:red">Not loaded</span> <small>[<span><a href="?p=' . P . '&do=_update_maxmind_geoip" class="nounderline">Load</a></span>]</small>';
        } else {
            $geo_ip_status = '<span style="color:green">Loaded</span> <small>[<span><a href="?p=' . P . '&do=_update_maxmind_geoip" class="nounderline">Update</a></span>]</small> ' . ($last_geo_ip_ts ? '<small>[' . date(Constants::FORMAT_CMS_DATETIME_FORMAT, $last_geo_ip_ts) . ']</small>' : '');
        }
        unset($last_geo_ip_ts);

        // Front assets validation
        if (isset($settings['last_assets_invalidate_time'])) {
            $last_invalidate_time = $settings['last_assets_invalidate_time'];
        } else {
            // Save fresh
            $last_invalidate_time = NOW;
            $setting = new SettingEntity();
            $setting->setValue(NOW);
            $setting->setName('last_assets_invalidate_time');
            $setting->save();
        }


        // Google Sitemap XML
        $xml = DIR_BASE . 'sitemap.gz';
        $xml = file_exists($xml) ? filemtime($xml) : false;
        $submit_xml = Cacher::getInstance()->getDefaultCacher()->get('cms_tools_submit_structure_xml');

        echo CmsForm::getInstance()
            ->addFieldBlock('Site',
                [
                    ['name' => 'Filemanager', 'field' => CmsHtml::getInstance('')
                        ->setValue('<a href="" data-popup-width="700" data-popup-height="720" data-popup-url="?p=filemanager&nomenu&path=' . DIR_PUBLIC_URL . '&cache=' . NOW . '" onclick="return false;">Open</a>'),
                    ],
                    ['name' => 'File Robots.txt', 'field' => CmsHtml::getInstance('')
                        ->setValue(file_exists(DIR_BASE . 'robots.txt') ? '<span style="color: green">Exists</span>' : '<span style="color: red">Does not exist</span> [<a href="?p=' . P . '&do=_create_robots_txt">Create</a>]'),
                    ],
                    ['name' => 'Compress assets', 'field' => CmsHtml::getInstance('')->setValue('<a href="?p=' . P . '&do=compress_scripts">Compress</a> [<var><a href="?p=' . P . '&do=compress_scripts&remove">Remove compressed files</a></var>]')->setHintText('GZip CSS and JS files')],
                    ['name' => 'Invalidate assets', 'field' => CmsHtml::getInstance('')->setValue('<a href="?p=' . P . '&do=_invalidate_assets">Invalidate</a> [Current is ' . date('m.d.Y H:i:s', $last_invalidate_time) . ']')->setHintText('Set fresh timestamp to frontend assets, prevents caching')],

                ]
            )
            ->addFieldBlock('CMS',
                [
                    ['name' => 'Credentials', 'field' => CmsHtml::getInstance('')
                        ->setValue('<a href="?p=' . P . '&do=credentials">View</a>'),
                    ],
                    ['name' => 'Application log', 'field' => CmsHtml::getInstance('')
                        ->setValue('<a href="?p=' . P . '&do=application_log">View</a>'),
                    ],
                    ['name' => 'Import Objects', 'field' => CmsHtml::getInstance('')->setValue('<a href="?p=' . P . '&do=import_objects">Import...</a>')->setHintText('Upload exported module Objects')],
                    ['name' => 'Error log', 'field' => CmsHtml::getInstance('')->setValue('<a href="?p=' . P . '&do=error_log">View</a>')],

                ]
            )
            ->addFieldBlock('Cache', [
                    ['name' => 'Entire', 'field' => CmsHtml::getInstance('')->setValue('<a href="?p=' . P . '&do=_clear_cache" onclick="return confirm(\'' . __('Are you sure?') . '\');">Clear</a>')],
                    ['name' => 'Files', 'field' => CmsHtml::getInstance('')->setValue('<a href="?p=' . P . '&do=_clear_cache_files" onclick="return confirm(\'' . __('Are you sure?') . '\');">Clear</a>')],
                    ['name' => 'Images', 'field' => CmsHtml::getInstance('')->setValue('<a href="?p=' . P . '&do=_clear_cache_images" onclick="return confirm(\'' . __('Are you sure?') . '\');">Clear</a>')],
                    ['name' => 'Memcache', 'field' => CmsHtml::getInstance('')->setValue('<a href="?p=' . P . '&do=_clear_cache_memcache">Clear</a>')],
                    ['name' => 'MemcacheD', 'field' => CmsHtml::getInstance('')->setValue('<a href="?p=' . P . '&do=_clear_cache_memcached">Clear</a>')],
                ]
            )
            ->addFieldBlock('Server',
                [
                    ['name' => 'PHP Info', 'field' => CmsHtml::getInstance('')->setValue('<a href="?p=' . P . '&do=php_info">View</a>')],
                    ['name' => 'Hardware Info', 'field' => CmsHtml::getInstance('')->setValue('<a href="?p=' . P . '&do=server">View</a>')],
                    ['name' => 'File statistics and backups', 'field' => CmsHtml::getInstance('')->setValue('<a href="?p=' . P . '&do=filestats">View</a>')],
                ]
            )
            ->addFieldBlock('Database',
                [
                    ['name' => 'Repair and optimize', 'field' => CmsHtml::getInstance('')->setValue(Cacher::getInstance()->getDefaultCacher()->get('cms_tools_repair_and_optimize_db') ? '<span style="color: green">No need</span>' : '<a href="?p=' . P . '&do=_repair_and_optimize_db">Repair and Optimize</a>')],
                    ['name' => 'Statistics', 'field' => CmsHtml::getInstance('')->setValue('<a href="?p=' . P . '&do=db_statistics">View</a>')],
                ]
            )
            ->addFieldBlock('External resources',
                [
                    ['name' => 'Pages in Google', 'field' => CmsHtml::getInstance('')
                        ->setValue('<a href="http://www.google.com/search?q=site%3A' . urlencode(CFG_DOMAIN) . '" target="_blank">Show</a>'),
                    ],
                    ['name' => 'Generate Structure XML', 'field' => CmsHtml::getInstance('')
                        ->setValue('<a href="?p=' . P . '&do=_generate_structure_xml">Generate</a>' . ($xml ? ' (' . date(Constants::FORMAT_CMS_DATETIME_FORMAT, $xml) . ')' : NULL)),
                    ],
                    ['name' => 'Structure XML to Google', 'field' => CmsHtml::getInstance('')
                        ->setValue('<a href="?p=' . P . '&do=_submit_structure_xml">Submit</a>' . ($submit_xml ? ' (' . date(Constants::FORMAT_CMS_DATETIME_FORMAT, $submit_xml) . ')' : NULL)),
                    ],
                    ['name' => 'GEO IP database', 'field' => CmsHtml::getInstance('')
                        ->setValue($geo_ip_status),
                    ],
                ]
            )
            ->addFieldBlock('Updates',
                [
                    ['name' => 'Update files from git', 'field' => CmsHtml::getInstance('update_from_git')->setValue('<a href="?p=' . P . '&do=_run_updater&files">Update</a>')->setHintText('Pulls files from git repository "' . CFG_GIT_BRANCH . '" branch. Requires git, repo access, run as web-user')],
                    ['name' => 'Update CMS libraries', 'field' => CmsHtml::getInstance('update_from_composer')->setValue('<a href="?p=' . P . '&do=_run_updater&composer">Update</a>')->setHintText('Pulls files from git repository "' . CFG_GIT_BRANCH . '" branch. Requires git, repo access, run as web-user')],
                    ['name' => 'git history', 'field' => CmsHtml::getInstance('git_history')->setValue('<a href="?p=' . P . '&do=git_history">View</a>')->setHintText('Pulls files from git repository "' . CFG_GIT_BRANCH . '" branch. Requires git, repo access, run as web-user')],
                    ['name' => 'Run DB migrations', 'field' => CmsHtml::getInstance('run_db_migrations')->setValue('<a href="?p=' . P . '&do=_run_updater&db">Update</a>')->setHintText('Runs any before un-run migrations from folder "' . DIR_MIGRATIONS_URL . '"')],
                    ['name' => 'Run PHPUnit tests', 'field' => CmsHtml::getInstance('run_phpunit_tests')->setValue('<a href="?p=' . P . '&do=_run_updater&tests">Update</a>')->setHintText('Runs PHPUnit test of entire libraries and project files')],
                ]
            );
    }

    public function development()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _development()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function backup_files()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function backup_db()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function php_info()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function credentials()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _update_maxmind_geoip()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function settings()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _update_settings()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function services()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function services_add()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _services_add()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _run_service()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function services_edit()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _services_edit()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _services_delete()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function analyze_db_queries()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _clear_db_analyzer_table()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _reset_permissions()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _repair_and_optimize_db()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function db_statistics()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _generate_structure_xml()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _submit_structure_xml()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _create_robots_txt()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function server()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function compress_scripts()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function application_log()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _error_log_clear()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function front_log()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _front_log_clear()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function error_log()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function error_log_view()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function filestats()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _clear_cache()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _clear_cache_memcached()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _clear_cache_memcache()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _clear_cache_files()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _clear_cache_images()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _run_updater()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function import_objects()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _import_objects()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function git_history()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _invalidate_assets()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function code_generator()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _generate_method()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _generate_module()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _generate_module_entity()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _generate_entity_field()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _ajax_get_module_entities()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function entity_editor()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _entity_editor()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }
}
