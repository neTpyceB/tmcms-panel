<?php
declare(strict_types=1);

use TMCms\Files\FileSystem;
use TMCms\Modules\ModuleManager;
use TMCms\Strings\Converter;

defined('INC') or exit;

if (!$_POST['module_name']) {
    error('Module name require');
}

if (!$_POST['entity_name']) {
    error('Entity name require');
}

$module_name = $_POST['module_name'];

$entity_name = Converter::toCamelCase(str_replace([' '], ['_'], $_POST['entity_name']));

if (!ModuleManager::moduleExists($module_name)) {
    error('Module does not exist');
}

$module_folder = DIR_MODULES . $module_name;
$entity_folder = $module_folder . '/Entity/';

FileSystem::mkDir($entity_folder);

// Check file exists
$entity_file = $entity_folder . $entity_name . 'Entity.php';
if (!file_exists($entity_file)) {
    // Create controller file
    touch($entity_file);
}

$entity_class_code = '<?php
declare(strict_types=1);

namespace TMCms\Modules\\'. ucfirst($module_name) .'\Entity;

use TMCms\Orm\Entity;

/**
 * Class '. $entity_name .'Entity
 * @package TMCms\Modules\\'. ucfirst($module_name) .'\Entity
 *
 * @method string getTitle()
 */
class '. $entity_name .'Entity extends Entity
{
    protected $translation_fields = ['. $entity_name .'EntityRepository::FIELD_TITLE];
}';

file_put_contents($entity_file, $entity_class_code);

// Check file exists
$repository_file = $entity_folder . $entity_name . 'EntityRepository.php';
if (!file_exists($repository_file)) {
    // Create controller file
    touch($repository_file);
}

$repository_class_code = '<?php
declare(strict_types=1);

namespace TMCms\Modules\\'. ucfirst($module_name) .'\Entity;

use TMCms\Orm\EntityRepository;
use TMCms\Orm\TableStructure;

/**
 * Class '. $entity_name .'EntityRepository
 * @package TMCms\Modules\\'. ucfirst($module_name) .'\Entity
 */
class '. $entity_name .'EntityRepository extends EntityRepository
{
    const FIELD_TITLE = \'title\';

    protected $translation_fields = [self::FIELD_TITLE];

    protected $table_structure = [
        \'fields\'  => [
            self::FIELD_TITLE       => [
                \'type\'            => TableStructure::FIELD_TYPE_TRANSLATION,
            ],
            \'active\'              => [
                \'type\' => TableStructure::FIELD_TYPE_BOOL,
            ],
            \'order\'               => [
                \'type\' => TableStructure::FIELD_TYPE_UNSIGNED_INTEGER,
            ],
        ],
    ];
}';

file_put_contents($repository_file, $repository_class_code);

back();
