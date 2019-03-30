<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface UsersRepository
{
    public function search(string $query = ""): Collection;
}
