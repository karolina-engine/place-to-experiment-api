<?php 

namespace Karolina\Setting;

trait SettingTrait
{
    public function setting($variable)
    {
        return $this->settings->get($variable);
    }

    public function setSetting($variable, $value)
    {
        $this->settings->set($variable, $value);
    }

    public function settingIsSet($variable)
    {
        return $this->settings->variableIsSet($variable);
    }

    public function setSettingsGroup(\Karolina\Setting\SettingsGroup $group)
    {
        $this->settings = $group;
    }

    public function addSettings(array $newSettings)
    {
        foreach ($newSettings as $variable => $value) {
            $this->setSetting($variable, $value);
        }
    }

    public function getAllSettings()
    {
        return $this->settings->getAll();
    }
}
