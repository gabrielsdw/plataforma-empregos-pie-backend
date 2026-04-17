<?php

namespace App\Repositories\Api;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Log;

class AuthRepository extends BaseRepository
{
    public function login(array $data)
    {
        Log::info("Boa!");
        return [];
    }
}