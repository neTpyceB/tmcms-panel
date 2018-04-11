<?php
declare(strict_types=1);

namespace TMCms\Admin\Structure;

defined('INC') or exit;

use TMCms\Admin\Menu;
use TMCms\Admin\Structure\Entity\PageComponentCustomEntityRepository;
use TMCms\Admin\Structure\Entity\PageComponentHistoryRepository;
use TMCms\Admin\Structure\Entity\PageComponentEntityRepository;
use TMCms\Admin\Structure\Entity\PageEntityRepository;
use TMCms\Admin\Users\Entity\UsersMessageEntity;
use TMCms\Files\FileSystem;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\Routing\Entity\PageComponentsDisabledEntityRepository;
use TMCms\Routing\Entity\PagesDomainEntity;
use TMCms\Routing\Entity\PagesDomainEntityRepository;
use TMCms\Routing\Entity\PagesWordEntity;
use TMCms\Routing\Entity\PagesWordEntityRepository;
use TMCms\Routing\Languages;
use TMCms\Traits\singletonInstanceTrait;

$pages_collection = new PageEntityRepository();
$pages_count = $pages_collection->getCountOfObjectsInCollection();
$language_count = Languages::getTotalCountOfLanguage();

if (!$pages_count) {
    Menu::getInstance()
        ->addLabelForMenuItem((string)$pages_count, '_default', 'structure');
}
if (!$language_count) {
    Menu::getInstance()
        ->addLabelForMenuItem((string)$language_count, 'languages', 'structure');
}


class CmsStructure
{
    use singletonInstanceTrait;

    /** PAGES */

    /**
     *
     * @param $data
     *
     * @return mixed
     */
    public static function _default_callback($data)
    {
        $link_to_components = '?p=structure&do=edit_components&id=';

        foreach ($data as & $v) {
            if (!$v['pid'] && $v['active'] && $v['in_menu']) {
                $v['title'] = '<strong>' . $v['title'] . '</strong>';
            }

            $v['title'] = $v['title'] . ' [' . $v['location'] . ']';
            $v['properties'] = '<a class="jsButton" href="?p=' . P . '&do=edit_page&id=' . $v['id'] . '">Properties</a>';
            $v['view'] = '<a class="jsButton" href="?p=' . P . '&do=_view_page_on_frontend&id=' . $v['id'] . '" target="_blank">View</a>';
            $v['title'] = '<a href="' . $link_to_components . $v['id'] . '">' . $v['title'] . '</a>';
        }

        return $data;
    }

    public static function _aliases_callback($data)
    {
        foreach ($data as &$v) {
            // Alias href
            $link = 'http://' . CFG_DOMAIN . '/' . $v['name'] . '/';
            $v['alias'] = '<a target="_blank" href="' . $link . '" onclick="window.open(\'' . $link . '\'); return false;">' . $link . '</a>';

            // Original href
            $link = 'http://' . CFG_DOMAIN . $v['href'];
            $v['href'] = '<a target="_blank" href="' . $link . '" onclick="window.open(\'' . $link . '\'); return false;">' . $link . '</a>';
        }

        return $data;
    }

    public static function _words_callback($data)
    {
        foreach ($data as $k => &$v) {
            $data[$v['id']] = $data[$k];

            unset ($data[$v['id']]);

            if (!$v['word']) {
                $v['word'] = '<span style="color: red">---</span>';
            }
        }

        return $data;
    }

    /**
     * Main View of Pages Tree
     */
    public function _default()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function add_page()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    /**
     * Action for Add Page
     */
    public function _add_page()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function pages_parent_tree()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function edit_page()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _edit_page()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _delete_page()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _menu_page()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _active_page()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _order_page()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _view_page_on_frontend()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function copy_branch()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _copy_branch()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    /** COMPONENTS */
    public function edit_components()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _edit_components()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    /** LANGU?AGES */

    public function languages()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function languages_add()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    /**
     * @return CmsForm
     */
    public function _languages_add_edit_form(): CmsForm
    {
        $languages = Languages::getPairs();

        $fields = [
            'short' => [
                'title'    => '2-letter code',
                'validate' => [
                    'minlength' => 2,
                    'maxlength' => 2,
                ],
                'required' => true,
            ],
            'full'  => [
                'title'    => 'Name',
                'validate' => [
                    'minlength' => 1,
                ],
                'required' => true,
            ],
        ];

        if ($languages) {
            $fields['copy_from'] = [
                'title'   => 'Copy Pages from...',
                'options' => [0 => '--- Do not copy ---'] + $languages,
                'hint'    => 'Copy Structure pages and its\' content',
            ];
            $fields['copy_translations'] = [
                'title' => 'Copy translations',
                'type'  => 'checkbox',
                'hint'  => 'Copy all entities in Modules',
            ];
        }

        $form = CmsFormHelper::outputForm([
            'action' => '?p=' . P . '&do=_language_add',
            'button' => __('Add'),
            'cancel' => true,
            'fields' => $fields,
        ]);

        return $form;
    }

    public function _language_add()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    /**
     * Action for Copy Pages, helper
     *
     * @param     $id
     * @param int $pid
     *
     * @return int
     */
    public function copy_pages($id, $pid = 0): int
    {
        $data = sql_prepare(q_assoc_row('SELECT * FROM `cms_pages` WHERE `id` = "' . $id . '"'));

        // No pages
        if (!$data) {
            return 0;
        }

        unset($data['id']);
        $data['pid'] = (int)$pid;
        $new_id = q('INSERT INTO `cms_pages` (`' . implode('`,`', array_keys($data)) . '`) VALUES ("' . implode('","', $data) . '")', true);
        $this->copy_components($id, $new_id);
        foreach (q_assoc_iterator('SELECT `id` FROM `cms_pages` WHERE `pid` = "' . $id . '"') as $v) {
            $this->copy_pages($v['id'], $new_id);
        }

        return (int)$new_id;
    }

    /**
     * Action for Copy Components
     *
     * @param $from_id
     * @param $to_id
     */
    public function copy_components($from_id, $to_id)
    {
        // Create db if not already done
        new PageComponentEntityRepository();
        new PageComponentsDisabledEntityRepository();
        new PageComponentHistoryRepository();
        new PageComponentCustomEntityRepository();

        // Components
        q('INSERT INTO `cms_pages_components` (`page_id`, `component`, `data`) SELECT "' . $to_id . '" AS `page_id`, `component`, `data` FROM `cms_pages_components` WHERE `page_id` = "' . $from_id . '"');
        // Disabled
        q('INSERT INTO `cms_pages_components_disabled` (`page_id`, `class`) SELECT "' . $to_id . '" AS `page_id`, `class` FROM `cms_pages_components_disabled` WHERE `page_id` = "' . $from_id . '"');
        // History
        q('INSERT INTO `cms_pages_components_history` (`page_id`, `user_id`, `version`, `component`, `data`, `ts`) SELECT "' . $to_id . '" AS `page_id`, `user_id`, `version`, `component`, `data`, `ts` FROM `cms_pages_components_history` WHERE `page_id` = "' . $from_id . '"');
        // Custom
        q('INSERT INTO `cms_pages_components_custom` (`page_id`, `component`, `tab`, `name`, `value`, `order`, `active`) SELECT "' . $to_id . '" AS `page_id`, `component`, `tab`, `name`, `value`, `order`, `active` FROM `cms_pages_components_custom` WHERE `page_id` = "' . $from_id . '"');
    }

    public function _languages_delete()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function languages_edit()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _edit_language()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    /** TRANSLATION WORDS */
    public function words()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function words_add()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _words_add()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function words_edit()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _words_edit()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _words_delete()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _find_not_used_words()
    {
        $used_words = [];

        foreach (FileSystem::scanDirs(DIR_FRONT) as $path) {
            if ($path['type'] !== 'file') {
                continue;
            }
            if (pathinfo($path['name'], PATHINFO_EXTENSION) !== 'php') {
                continue;
            }

            $fileContents = file_get_contents($path['full']);

            preg_match_all("/ w\('(.*?)'/", $fileContents, $matches);

            if ($matches && isset($matches[1])) {
                foreach ($matches[1] as $match) {
                    $used_words[$match] = $match;
                }
            }
        }

        $_SESSION['cms_used_words'] = serialize($used_words);

        back();
    }

    public function _delete_not_used_words()
    {
        if (!isset($_SESSION['cms_used_words'])) {
            error('No list to delete');
        }

        $used_words = unserialize($_SESSION['cms_used_words']);

        $words = new PagesWordEntityRepository();
        /** @var PagesWordEntity $word */
        foreach ($words->getAsArrayOfObjects() as $word) {
            if (!isset($used_words[substr($word->getName(), 0, -3)])) {
                $word->deleteObject();
            }
        }

        back();
    }

    /** ALIASES */
    public function aliases()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function aliases_add()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _aliases_add()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _aliases_delete()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function aliases_edit()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _aliases_edit()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _aliases_is_landing()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    /** HISTORY */
    public function page_history()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _restore_page_history()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function permissions()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _permissions()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function plugins()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _ajax_render_plugin_fields()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _ajax_delete_message()
    {
        $message = new UsersMessageEntity($_GET['id']);

        if ((int)$message->getToUserId() !== USER_ID) {
            return;
        }

        $message->deleteObject();

        echo '1';
    }

    public function customs()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _customs()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _customs_order()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _customs_delete()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function domains()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function domains_add()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _domains_add()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _domains_delete()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function domains_edit()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    public function _domains_edit()
    {
        require_once __DIR__ . '/Pages/' . __FUNCTION__ . '.php';
    }

    /**
     * @param null|PagesDomainEntity $domain
     *
     * @return CmsForm
     */
    public function _domains_add_edit_form($domain = NULL): CmsForm
    {
        $domains = new PagesDomainEntityRepository;
        $form = CmsFormHelper::outputForm($domains->getDbTableName(), [
            'button' => 'Add',
            'data'   => $domain,
            'fields' => [
                'name'      => [
                    'title' => 'Website name',
                ],
                'urls'      => [
                    'type' => 'textarea',
                    'hint' => 'One host per line, e.g. domain.com, sub.site.net, sitename.org',
                ],
                'languages' => [
                    'type'    => 'multiselect',
                    'options' => Languages::getPairs(),
                ],
            ],
        ]);

        return $form;
    }
}
