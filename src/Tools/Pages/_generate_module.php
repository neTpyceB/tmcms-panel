<?php
declare(strict_types=1);

use TMCms\Files\FileSystem;

defined('INC') or exit;

if (!$_POST['module_name']) {
    error('Module name require');
}

$module_name = $_POST['module_name'];

FileSystem::mkDir(DIR_MODULES);

$module_folder = DIR_MODULES . $module_name;

FileSystem::mkDir($module_folder);

// Check Module file exists
$module_file = $module_folder . '/Module'. ucfirst($module_name) . '.php';
if (!file_exists($module_file)) {
    // Create controller file
    touch($module_file);
}
$module_class_code = '<?php
declare(strict_types=1);

namespace TMCms\Modules\\'. ucfirst($module_name) .';

use TMCms\Modules\IModule;
use TMCms\Traits\singletonInstanceTrait;

/**
 * Class Module' . ucfirst($module_name) . '
 * @package TMCms\Modules\\' . ucfirst($module_name) . '
 */
class Module' . ucfirst($module_name) . ' implements IModule
{
    use singletonInstanceTrait;
}';

file_put_contents($module_file, $module_class_code);


// Check Cms file exists
$cms_file = $module_folder . '/Cms'. ucfirst($module_name) . '.php';
if (!file_exists($cms_file)) {
    // Create controller file
    touch($cms_file);
}
$cms_class_code = '<?php
declare(strict_types=1);

namespace TMCms\Modules\\'. ucfirst($module_name) .';

/**
 * Class Cms' . ucfirst($module_name) . '
 * @package TMCms\Modules\\' . ucfirst($module_name) . '
 */
class Cms' . ucfirst($module_name) . '
{

}';

file_put_contents($cms_file, $cms_class_code);


back();
