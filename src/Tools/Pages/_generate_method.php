<?php

use TMCms\Files\FileSystem;

defined('INC') or exit;

if (!$_POST['component_class']) {
    error('Class name require');
}

if (!$_POST['component_method']) {
    $_POST['component_method'] = 'index';
}

FileSystem::mkDir(DIR_FRONT_CONTROLLERS);
FileSystem::mkDir(DIR_FRONT_VIEWS);

// Check controller file exists
$controller_file = DIR_FRONT_CONTROLLERS . $_POST['component_class'] . '.php';
if (!file_exists($controller_file)) {
    // Create controller file
    touch($controller_file);
}

// Check controller class exists
require_once $controller_file;
$controller_class = ucfirst($_POST['component_class']) . 'Controller';
if (!class_exists($controller_class)) {
    $controller_class_code = '<?php
declare(strict_types=1);

use TMCms\Routing\Controller;

class ' . $controller_class . ' extends Controller
{
    
}';

    file_put_contents($controller_file, $controller_class_code, FILE_APPEND);
}

// Check controller method exists
if (!method_exists($controller_class, $_POST['component_method'])) {
    $current_controller_file_content = trim(file_get_contents($controller_file));
    $current_controller_file_content = substr($current_controller_file_content, 0, -1);
    $current_controller_file_content = $current_controller_file_content . '
    public function ' . $_POST['component_method'] . '()
    {

    }
}';

    file_put_contents($controller_file, $current_controller_file_content);
}

// Check view file exists
$view_file = DIR_FRONT_VIEWS . $_POST['component_class'] . '.php';
if (!file_exists($view_file)) {
    // Create view file
    touch($view_file);
}

// Check view class exists
require_once $view_file;
$view_class = ucfirst($_POST['component_class']) . 'View';
if (!class_exists($view_class)) {
    $view_class_code = '<?php
declare(strict_types=1);

use TMCms\Routing\View;

class ' . $view_class . ' extends View
{
    
}';

    file_put_contents($view_file, $view_class_code, FILE_APPEND);
}

// Check view method exists
if (!method_exists($view_class, $_POST['component_method'])) {
    $current_view_file_content = trim(file_get_contents($view_file));
    $current_view_file_content = substr($current_view_file_content, 0, -1);
    $current_view_file_content = $current_view_file_content . '
    public function ' . $_POST['component_method'] . '()
    {

    }
}';

    file_put_contents($view_file, $current_view_file_content);
}

// Check view separate file and view
if (!file_exists(DIR_FRONT_VIEWS . $_POST['component_class'] . '/')) {
    FileSystem::mkDir(DIR_FRONT_VIEWS . $_POST['component_class'] . '/');
}
if (!file_exists(DIR_FRONT_VIEWS . $_POST['component_class'] . '/' . $_POST['component_method'] . '.php')) {
    touch(DIR_FRONT_VIEWS . $_POST['component_class'] . '/' . $_POST['component_method'] . '.php');

    $view_file_content = '<?php
declare(strict_types=1);

use TMCms\Routing\MVC;

/** @var MVC $this */
/** @var ' . $view_class . ' $view */
$view = $this->getCurrentViewObject();

?>
';
    file_put_contents(DIR_FRONT_VIEWS . $_POST['component_class'] . '/' . $_POST['component_method'] . '.php', $view_file_content);
}

back();
