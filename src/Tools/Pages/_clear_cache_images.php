<?php

use TMCms\Admin\Messages;
use TMCms\Files\FileSystem;
use TMCms\Log\App;

FileSystem::remdir(DIR_IMAGE_CACHE);

App::add('Image cache cleared');
Messages::sendGreenAlert('Image cache cleared');

back();