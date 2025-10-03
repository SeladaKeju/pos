<?php

namespace App\Interfaces;   

interface AuthRepositoryInterface
{
    public function register(array $userData);
    public function login(array $credentials);
    public function logout();
    public function getUser();
}