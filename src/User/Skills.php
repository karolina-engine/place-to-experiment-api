<?php
namespace Karolina\User;

class Skills
{
    private $skills = array();

    public function set(array $skills)
    {
        $newSkills = array();

        foreach ($skills as $skill) {
            $skill = strtolower($skill); // lowercase

            if ($this->isValid($skill) and !in_array($skill, $newSkills)) {
                $newSkills[] = $skill;
            }
        }

        $this->skills = $newSkills;
    }

    private function isValid($skill)
    {
        return true;
    }

    
    public function getAll()
    {
        return $this->skills;
    }
}
