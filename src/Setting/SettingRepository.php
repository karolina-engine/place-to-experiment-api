<?php

namespace Karolina\Setting;

class SettingRepository
{
    public function getAll()
    {
        $settingsGroup = new \Karolina\Setting\SettingsGroup();

        $models = \Karolina\Database\Table\Setting::all();

        foreach ($models as $model) {
            $settingsGroup->set($model->variable, $model->value);
        }

        return $settingsGroup;
    }
}
