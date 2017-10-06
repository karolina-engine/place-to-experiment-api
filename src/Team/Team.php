<?php 

namespace Karolina\Team;

use Karolina\User\User;

class Team
{
    private $members = array();
    protected $id;


    public function addMember(User $user, $roles = array())
    {
        $this->members[$user->getId()]['user'] = $user;
        $this->members[$user->getId()]['roles'] = $roles;
    }

    public function removeMember(User $user)
    {
        if ($this->isInTeam($user)) {
            unset($this->members[$user->getId()]);
        }
    }

    public function isInTeam(User $user)
    {
        if (isset($this->members[$user->getId()])) {
            return true;
        } else {
            return false;
        }
    }

    public function hasRole(User $user, $roleName)
    {
        if (!$this->isInTeam($user)) {
            return false;
        }

        $member = $this->members[$user->getId()];

        if (in_array($roleName, $member['roles'])) {
            return true;
        } else {
            return false;
        }
    }

    public function getMembers()
    {
        return $this->members;
    }
}
