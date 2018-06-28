<?php

namespace Karolina\Stats;
use Karolina\Experiment\ExperimentInteractor;
use Karolina\User\UserInteractor;

class Dashboard
{
    private $experimentInteractor;
    private $userInteractor;


    public function __construct(ExperimentInteractor $experimentInteractor, UserInteractor $userInteractor)
    {

        $this->experimentInteractor = $experimentInteractor;
        $this->userInteractor = $userInteractor;

    }

    public function getStats ($experimentShowIn = false) {

        $stats['experiments'] = $this->experimentInteractor->getStats($experimentShowIn);
        $stats['users'] = $this->userInteractor->getStats();
        return $stats;

    }

}
