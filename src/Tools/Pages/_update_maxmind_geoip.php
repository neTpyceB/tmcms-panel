<?php
declare(strict_types=1);

use TMCms\Admin\Messages;
use TMCms\Log\App;
use TMCms\Network\MaxMindGeoIP;

defined('INC') or exit;

set_time_limit(120);

MaxMindGeoIP::updateDatabase();

App::add('GeoIP database updated');
Messages::sendGreenAlert('GeoIP database updated');

back();