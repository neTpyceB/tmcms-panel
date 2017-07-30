<?php
declare(strict_types=1);

use TMCms\DB\Entity\DbQueryAnalyzerDataEntityRepository;
use TMCms\DB\Entity\DbQueryAnalyzerEntityRepository;
use TMCms\DB\SQL;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsTableHelper;
use TMCms\Routing\Structure;

defined('INC') or exit;

// Ensure tables exist
new DbQueryAnalyzerEntityRepository();
new DbQueryAnalyzerDataEntityRepository();

$tbl_lst = SQL::getTableInfo('cms_db_queries_analyzer_data'); // Last Scan Time
$tbl_lst = (int)$tbl_lst['Comment'];

if (NOW - $tbl_lst > 86400 || isset($_GET['force'])) {

    // Have queries to analyze
    if (q_value('SELECT * FROM `cms_db_queries_analyzer` LIMIT 1')) {
        $tmp_tbl = SQL::swapTable('cms_db_queries_analyzer');
        q('ALTER TABLE `' . $tmp_tbl . '` ADD INDEX (`hash`)');
        $tmp_data = q_assoc_id(
            'SELECT `hash`, `query`, `path`, MIN(`tt`) AS `min_tt`, MAX(`tt`) AS `max_tt`, ROUND(AVG(`tt`), 3) AS `avg_tt`, COUNT(*) AS `total`
FROM `' . $tmp_tbl . '`
GROUP BY `hash`'
        );

        $page_titles = [];

        $qh = q(
            'SELECT `hash`, `min_tt`, `avg_tt`, `max_tt`, `total`, `uses_indexes`, `page_title`
FROM `cms_db_queries_analyzer_data`
WHERE `hash` IN ("' . implode('","', array_keys($tmp_data)) . '")'
        );

        while ($q = $qh->fetch(PDO::FETCH_ASSOC)) {
            $tmp =& $tmp_data[$q['hash']];
            if (isset($page_titles[$tmp['path']])) {
                $page_title =& $page_titles[$tmp['path']];
            } else {
                $page_title = q_value('SELECT `title` FROM `cms_pages` WHERE `id` = "' . Structure::getIdByPath($tmp['path']) . '"');
                $page_titles[$tmp['path']] = $page_title = sql_prepare($page_title);
            }

            // Save analyze
            q(
                'UPDATE `cms_db_queries_analyzer_data` SET
	`min_tt`="' . round(($q['min_tt'] * $q['total'] + $tmp['min_tt']) / ($q['total'] + $tmp['total']), 3) . '",
	`avg_tt`="' . round(($q['avg_tt'] * $q['total'] + $tmp['avg_tt']) / ($q['total'] + $tmp['total']), 3) . '",
	`max_tt`="' . round(($q['max_tt'] * $q['total'] + $tmp['max_tt']) / ($q['total'] + $tmp['total']), 3) . '",
	`total`="' . ($q['total'] + $tmp['total']) . '",
	`uses_indexes`="' . (int)@SQL::usesAllIndexes($tmp['query']) . '"' .
                ($page_title ? ', `page_title`="' . $page_title . '" ' : NULL)
                . 'WHERE `hash`="' . $q['hash'] . '"'
            );
        }

        foreach ($tmp_data as &$q) {
            if (isset($page_titles[$q['path']])) {
                $page_title =& $page_titles[$q['path']];
            } else {
                $page_title = q_value('SELECT `title` FROM `cms_pages` WHERE `id` = "' . Structure::getIdByPath($q['path']) . '"');
                $page_titles[$q['path']] = $page_title;
            }

            // Save aggregated data
            q(
                'INSERT INTO `cms_db_queries_analyzer_data` (`hash`, `query`, `min_tt`, `avg_tt`, `max_tt`, `total`, `path`, `uses_indexes`, `page_title`)
VALUES ("' . $q['hash'] . '", "' . sql_prepare($q['query']) . '", "' . $q['min_tt'] . '", "' . $q['avg_tt'] . '", "' . $q['max_tt'] . '", "' . $q['total'] . '", "' . sql_prepare($q['path']) . '", "' . (int)SQL::usesAllIndexes($q['query']) . '", "' . sql_prepare($page_title) . '")'
                , 0);
        }
        unset($q);

        q('DROP TABLE `' . $tmp_tbl . '`');
    }
    SQL::setTableComment('cms_db_queries_analyzer_data', NOW);

    if (isset($_GET['force'])) {
        back();
    }
}

BreadCrumbs::getInstance()
    ->addCrumb('Database query analyze')
    ->addAction('Rescan', '?p=' . P . '&do=' . P_DO . '&force')
    ->addAction('Clear', '?p=' . P . '&do=clear_db_analyzer_table', ['confirm' => true]);

$data = new DbQueryAnalyzerDataEntityRepository;

echo CmsTableHelper::outputTable([
    'data'    => $data,
    'columns' => [
        'query'        => [],
        'uses_indexes' => [
            'title' => 'Indexes',
            'type'  => 'done',
            'order' => true,
        ],
        'min_tt'       => [
            'title' => 'Min. time taken',
            'order' => true,
        ],
        'avg_tt'       => [
            'title' => 'Avg. time taken',
            'order' => true,
        ],
        'max_tt'       => [
            'title' => 'Max. time taken',
            'order' => true,
        ],
        'total'        => [
            'title' => 'Total records',
            'order' => true,
        ],
    ],
]);