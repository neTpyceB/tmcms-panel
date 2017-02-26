<?php

use TMCms\HTML\Cms\CmsTable;
use TMCms\HTML\Cms\Column\ColumnTree;

defined('INC') or exit;

if (!isset($_GET['id'])) {
    return;
}
$id = (int)$_GET['id'];

$data = [];

foreach (q_assoc_iterator('SELECT `id`, `pid`, `title`, `active`, `in_menu`, `location` FROM `cms_pages` ORDER BY `order`') as $v) {
    // Main language page must be shown bold
	if (!$v['pid'] && $v['active'] && $v['in_menu']) {
        $v['title'] = '<strong>'. $v['title'] .'</strong>';
    }
    // Add select event
    $v['title'] = '<a style="cursor:pointer" onclick="selectLinkForSitemap(' . $v['id'] . '); return false;">' . $v['title'] . ' (' . $v['location'] . ')</a>';
	$data[] = $v;
}

?>
<script>
    function selectLinkForSitemap(page_id) {
        var modalWindow = $('#modal-popup_inner');
        modalWindow.trigger('popup:return_result', [page_id]);
        modalWindow.trigger('popup:close');
    }
</script>
<?php

echo CmsTable::getInstance()
    ->addData($data)
    ->disablePager()
    ->addColumn(ColumnTree::getInstance('id')
        ->setTitle('Page')
        ->setShowKey('title')
        ->allowHtml()
    )
;

die;