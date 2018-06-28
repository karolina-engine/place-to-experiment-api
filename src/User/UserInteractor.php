<?php

namespace Karolina\User;


class UserInteractor
{
    private $repository;

    public function __construct(UserRepository $repository)
    {

        $this->repository = $repository;
    }

    public function getStats () {

        return $this->repository->getUserStats();

    }

}