<?php

use TMCms\Config\Settings;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\Element\CmsButton;
use TMCms\HTML\Cms\Element\CmsCheckbox;
use TMCms\HTML\Cms\Element\CmsInputText;
use TMCms\HTML\Cms\Element\CmsSelect;
use TMCms\HTML\Cms\Widget\SitemapPages;
use TMCms\Routing\Languages;

defined('INC') or exit;

$data = Settings::getInstance()->init(true);

BreadCrumbs::getInstance()
    ->addCrumb(ucfirst(P))
    ->addCrumb('System Settings');

$date_select = [
    'H:i:s d.m.Y' => 'hours:minutes:seconds day.month.year',
    'H:i d.m.Y'   => 'hours:minutes day.month.year',
    'd.m.Y H:i:s' => 'day.month.year hours:minutes:seconds',
    'd.m.Y H:i'   => 'day.month.year hours:minutes',
    'd.m.Y'       => 'day.month.year',
    'Y.m.d'       => 'year.month.day',
    'd/m/Y'       => 'day/month/year',
    'Y/m/d'       => 'year/month/day',
    'd-m-Y'       => 'day-month-year',
    'Y-m-d'       => 'year-month-day',
];

$form = CmsForm::getInstance()
    ->setAction('?p=' . P . '&do=_update_settings')
    ->addData($data)
    ->setButtonSubmit(CmsButton::getInstance('Update'))
    ->addFieldBlock('Site',
        [
            ['name' => 'Common email', 'field' => CmsInputText::getInstance('common_email')],
            ['name' => 'Default date format', 'field' => CmsSelect::getInstance('common_date_format')->setOptions($date_select)->setHintText('Use function date() format units')],
            ['name' => 'Google Analytics ID', 'field' => CmsInputText::getInstance('google_analytics_code')->setHintText('Identificator of web resource<br>without UA-')],
            ['name' => 'Google API key', 'field' => CmsInputText::getInstance('google_api_key')->setHintText('API key for requests')],
            ['name' => 'Facebook Pixel key', 'field' => CmsInputText::getInstance('facebook_pixel_key')->setHintText('Facebook Pixel key for requests')],
            ['name' => 'Yandex.Metrika key', 'field' => CmsInputText::getInstance('yandex_metrika_key')->setHintText('Yandex.Metrika key for requests')],
            ['name' => 'Generate sitemap.xml', 'field' => CmsCheckbox::getInstance('autogenerate_sitemap_xml')->setHintText('Autogenerate sitemap.xml file when making changes in site Structure')],
            ['name' => 'Pages Clickmap', 'field' => CmsCheckbox::getInstance('clickmap')->setHintText('Vizualize user clicks on site. Requires jQuery using $() function, and according action files in ' . DIR_FRONT_API_URL)],
            ['name' => 'Permanent redirects', 'field' => CmsCheckbox::getInstance('permanent_redirects')->setHintText('When page location is changed - whether system should keep old location in Structure for redirecting users to new location')],
            ['name' => 'Page aliases enabled', 'field' => CmsCheckbox::getInstance('page_aliases_enabled')->setHintText('Enable checking for page short alias every time user comes to website. Requires to have aliases created.')],
            ['name' => 'Enable GDPR control', 'field' => CmsCheckbox::getInstance('gdpr')->setHintText('Enable Cookies and Session user control.')],
        ]
    )
    ->addFieldBlock('CMS',
        [
            ['name' => 'Allow user registration', 'field' => CmsCheckbox::getInstance('allow_registration')->setHintText('Allow registration requests of new users to CMS admin panel')],
            ['name' => 'Front site panel', 'field' => CmsCheckbox::getInstance('admin_panel_on_site')->setHintText('Show panel with general options on site if logged in admin. Requires jQuery')],
            ['name' => 'Disable CMS translations', 'field' => CmsCheckbox::getInstance('disable_cms_translations')->setHintText('Disable translations in admin panel elements. Content still is translated.')],
            ['name' => 'Show flags in language selector', 'field' => CmsCheckbox::getInstance('show_language_selector_flags')],
        ]
    )
    ->addFieldBlock('Languages',
        [
            ['name' => 'Skip LNG in generated links', 'field' => CmsCheckbox::getInstance('skip_lng_in_generated_links')->setHintText('Generated links for pages will be without /LNG/ as first part')],
            ['name' => 'Redirect to page without LNG', 'field' => CmsCheckbox::getInstance('skip_lng_redirect_to_same_page')->setHintText('If language part is disabled but URL contains it - force redirect to same page without language part')],
            ['name' => 'Save in Session', 'field' => CmsCheckbox::getInstance('lng_by_session')],
            ['name' => 'Save in Cookies', 'field' => CmsCheckbox::getInstance('lng_by_cookie')],
            ['name' => 'Search in HTTP header', 'field' => CmsCheckbox::getInstance('lng_by_http_header')],
            ['name' => 'Set by IP', 'field' => CmsCheckbox::getInstance('lng_by_ip')],
            ['name' => 'Default front language', 'field' => CmsSelect::getInstance('f_default_language')->setOptions(Languages::getPairs())],
        ]
    )
    ->addFieldBlock('Error 404 handling',
        [
            ['name' => 'Save location changes', 'field' => CmsCheckbox::getInstance('save_location_change_history')->setHintText('Save front page location changes, and if user opens old URL - redirect to new')],
            ['name' => 'Convert to GET params', 'field' => CmsCheckbox::getInstance('error_404_convert_transparent_get')->setHintText('If page not found that all extra parts of URL will be converted to $_GET params')],
            ['name' => 'Guess broken path', 'field' => CmsCheckbox::getInstance('guess_broken_path')->setHintText('Try to guess correct page when user mistypes some part of URL')],
            ['name' => 'Find page Structure', 'field' => CmsCheckbox::getInstance('error_404_find_in_structure')->setHintText('Redirect to location /LNG/404/ if page with such location exists')],
            ['name' => 'Go to main page', 'field' => CmsCheckbox::getInstance('error_404_go_to_main')->setHintText('Redirect to location /LNG/')],
            ['name' => 'Structure page', 'field' => CmsInputText::getInstance('error_404_page')->setWidget(new SitemapPages)->setHintText('Open exact page from Structure. Leave empty to disable')],
            ['name' => 'Show default', 'field' => CmsCheckbox::getInstance('error_404_show_default_page')->setHintText('Renders default 404 headers and status')],
        ]
    );

echo $form;
