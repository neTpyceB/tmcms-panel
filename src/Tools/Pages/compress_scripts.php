<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Files\FileSystem;
use TMCms\HTML\Cms\CmsFormHelper;
use TMCms\Log\App;

defined('INC') or exit;

$remove = isset($_GET['remove']);
$js_files = $css_files = $js_total = $css_total = $js_total_gz = $css_total_gz = 0;

foreach (FileSystem::scanDirs(DIR_ASSETS) as $f) {
    if ($f['type'] !== 'file') {
        continue;
    }

    switch (strtolower(pathinfo($f['name'], PATHINFO_EXTENSION))) {
        case 'js':
            if ($remove) {
                if (file_exists($f['full'] . '.gz')) {
                    unlink($f['full'] . '.gz');
                }
                continue 2;
            }
            ++$js_files;

            $data = file_get_contents($f['full']);
            $js_total += strlen($data);
            $data = gzencode($data, 6);
            $js_total_gz += strlen($data);
            file_put_contents($f['full'] . '.gz', $data);

            break;

        case 'css':
            if ($remove) {
                if (file_exists($f['full'] . '.gz')) {
                    unlink($f['full'] . '.gz');
                }
                continue 2;
            }

            ++$css_files;

            $data = file_get_contents($f['full']);
            $css_total += strlen($data);
            $data = gzencode($data, 6);
            $css_total_gz += strlen($data);
            file_put_contents($f['full'] . '.gz', $data);

            break;
    }
}

echo CmsFormHelper::outputForm([
    'fields' => [
        'CSS files'      => [
            'type'  => 'html',
            'value' => $css_files,
        ],
        'CSS compressed' => [
            'type'  => 'html',
            'value' => number_format(($css_total ? round(100 * ($css_total - $css_total_gz) / $css_total, 1) : 0)),
        ],
        'JS files'       => [
            'type'  => 'html',
            'value' => $js_files,
        ],
        'JS compressed'  => [
            'type'  => 'html',
            'value' => number_format(($js_total ? round(100 * ($js_total - $js_total_gz) / $js_total, 1) : 0)),
        ],
    ],
]);

App::add('Assets processed');
Messages::sendGreenAlert('Assets processed');