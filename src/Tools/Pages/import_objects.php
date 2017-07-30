<?php
declare(strict_types=1);

use TMCms\HTML\Cms\CmsFormHelper;

echo CmsFormHelper::outputForm([
    'action' => '?p=' . P . '&do=_import_objects',
    'button' => 'Upload new file',
    'fields' => [
        'files' => [
            'title' => 'Choose file',
            'type'  => 'file',
        ],
    ],
]);