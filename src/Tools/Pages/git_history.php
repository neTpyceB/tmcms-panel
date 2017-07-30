<?php
declare(strict_types=1);

exec("git log -20 --pretty=format:' (%ci) %h - %s' --abbrev-commit", $log);
foreach ($log as $l) {
    echo $l . '<br><br>';
}