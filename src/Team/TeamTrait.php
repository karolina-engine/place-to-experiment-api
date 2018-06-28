<?php

namespace Karolina\Team;
use Karolina\User\User;

trait TeamTrait {


    public function addToTeam (User $user, $roles = array()) {

    	$this->team->addMember($user, $roles);

        $teamMember = new TeamMember();
        $teamMember->setUser($user);

        $this->teamMembers[] = $teamMember;

    }

    public function removeFromTeam (User $user) {

    	$this->team->removeMember($user);    	

    }

    public function setTeam (Team $team) {

        $this->team = $team;

    }

    public function getTeamMembers () {

        return $this->team->getMembers();

    }

    public function getTeam () {

        return $this->team;
    }

    public function getTeamEmails () {

        return $this->team->getMembersEmails();
    }

    public function getTeamDocument () {

        $doc = array();
        foreach ($this->team->getMembers() as $memberId => $member) { 

            $doc[$memberId]['roles'] = $member['roles'];
            $doc[$memberId]['in_team'] = true;      
            $doc[$memberId]['first_name'] = $member['user']->getFirstName();      
            $doc[$memberId]['last_name'] = $member['user']->getLastName();

            if ($member['user']->getProfileImage()) {
                $doc[$memberId]['image'] = $member['user']->getProfileImage()->getFileName();      

            } else {

                $doc[$memberId]['image'] = null;      

            }

        }
        return $doc;

    }

}