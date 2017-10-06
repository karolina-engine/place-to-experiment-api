<?php 

namespace Karolina\Team;

class TeamMember
{
    protected $id;
    protected $experiment;
    protected $user;

    public function getId()
    {
        return $this->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(\Karolina\User\User $user)
    {
        $this->user = $user;
    }
}
