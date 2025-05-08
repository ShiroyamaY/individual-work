<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;

class UserController
{
    public function getTotalUsers(): int
    {
        return User::count();
    }
}
