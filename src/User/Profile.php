<?php 

namespace Karolina\User;

use Karolina\Language\Field;

class Profile
{
    private $shortDescription;
    private $description;


    public function setShortDescription($content)
    {
        if (is_a($content, 'Karolina\Language\Field')) {
            $this->shortDescription = $content;
        } else {
            $this->shortDescription = new Field($content);
        }
    }


    public function setDescription($content)
    {
        if (is_a($content, 'Karolina\Language\Field')) {
            $this->description = $content;
        } else {
            $this->description = new Field($content);
        }
    }


    public function getDescription()
    {
        return $this->description;
    }

    public function getShortDescription()
    {
        return $this->shortDescription;
    }
}
