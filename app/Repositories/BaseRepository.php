<?php

namespace App\Repositories;

abstract class BaseRepository
{
    public static bool $hasError = false;
    public static string $message = 'Success';
    public static int $statusCode = 200;
}