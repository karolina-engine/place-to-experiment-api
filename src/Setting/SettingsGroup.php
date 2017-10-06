<?php 

namespace Karolina\Setting;

class SettingsGroup
{
    private $variables = [];

    public function set($variableName, $value)
    {
        $this->variables[$variableName] = $value;
    }

    public function get($variableName)
    {
        if (isset($this->variables[$variableName])) {
            return $this->variables[$variableName];
        } else {
            throw new \Exception('Setting not found for variable: '.htmlentities($variableName));
        }
    }

    public function getAll()
    {
        return $this->variables;
    }

    public function variableIsSet($variableName)
    {
        if (isset($this->variables[$variableName])) {
            return true;
        } else {
            return false;
        }
    }
}
