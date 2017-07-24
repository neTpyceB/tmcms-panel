<?php
declare(strict_types=1);

use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsFormHelper;

defined('INC') or exit;

$data = [];

switch (strtolower(PHP_OS)) {
    case 'linux':
        ob_start();
        system('cat /proc/cpuinfo');
        $res = str_replace("\r", NULL, ob_get_clean());
        preg_match("/model name([^\n]+)/", $res, $cpu_model);
        $data['cpu_model'] = trim($cpu_model[1], "\t\r\n :");
        unset($cpu_model);

        preg_match("/cpu MHz([^\n]+)/", $res, $cpu_mhz);
        $data['cpu_mhz'] = round(trim($cpu_mhz[1], "\t\r\n :")) . ' Mhz';
        unset($cpu_mhz);

        preg_match("/cache size([^\n]+)/", $res, $l1_cache_size);
        $data['l1_cache_size'] = trim($l1_cache_size[1], "\t\r\n :");
        unset($l1_cache_size);

        ob_start();
        system('cat /proc/meminfo');
        $res = str_replace("\r", NULL, ob_get_clean());
        preg_match("/MemTotal([^\n]+)/", $res, $ram_total);
        $data['ram_total'] = round(trim($ram_total[1], "\t\r\n :") / 1024) . ' MB';
        unset($ram_total);

        preg_match("/MemFree([^\n]+)/", $res, $ram_free);
        $data['ram_free'] = round(trim($ram_free[1], "\t\r\n :") / 1024) . ' MB';
        unset($ram_free);

        preg_match("/SwapTotal([^\n]+)/", $res, $swap_total);
        $data['swap_total'] = round(trim($swap_total[1], "\t\r\n :") / 1024) . ' MB';
        unset($swap_total);

        preg_match("/SwapCached([^\n]+)/", $res, $swap_free);
        $data['swap_free'] = round(trim($swap_free[1], "\t\r\n :") / 1024) . ' MB';
        unset($swap_free);

        break;

    default:
        echo 'Not implemented yet for this OS.';
        break;
}

BreadCrumbs::getInstance()
    ->addCrumb('Server hardware information');

if ($data) {
    echo CmsFormHelper::outputForm([
        'data'   => $data,
        'fields' => [
            'cpu_model'     => [
                'type'  => 'html',
                'title' => 'CPU Model',
            ],
            'cpu_mhz'       => [
                'type'  => 'html',
                'title' => 'Mhz',
            ],
            'l1_cache_size' => [
                'type'  => 'html',
                'title' => 'L1 cache',
            ],
            'ram_total'     => [
                'type'  => 'html',
                'title' => 'RAM total',
            ],
            'ram_free'      => [
                'type'  => 'html',
                'title' => 'RAM free',
            ],
            'swap_total'    => [
                'type'  => 'html',
                'title' => 'Swap total',
            ],
            'swap_free'     => [
                'type'  => 'html',
                'title' => 'Swap total',
            ],
        ],
    ]);
}