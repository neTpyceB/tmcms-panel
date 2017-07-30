<?php
declare(strict_types=1);

use TMCms\Config\Entity\SettingEntity;
use TMCms\Config\Entity\SettingEntityRepository;

$setting = SettingEntityRepository::findOneEntityByCriteria(['name' => 'last_assets_invalidate_time']);
if (!$setting) {
    $setting = new SettingEntity();
    $setting->setName('last_assets_invalidate_time');
}

$setting->setValue(NOW);
$setting->save();

back();