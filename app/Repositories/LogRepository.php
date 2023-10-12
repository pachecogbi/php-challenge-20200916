<?php

namespace App\Repositories;

use App\Models\Log;

class LogRepository
{
    public function createLog($params)
    {
        return Log::create();
    }
}
