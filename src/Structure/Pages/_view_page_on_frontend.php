<?php

use TMCms\Routing\Structure;

defined('INC') or exit;

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    return;
}
$id =  &$_GET['id'];

$path = Structure::getPathById($id);

if (!$path) return;

go($path . (isset($_GET['clickmap']) ? '?cms_view_clickmap' : ''));