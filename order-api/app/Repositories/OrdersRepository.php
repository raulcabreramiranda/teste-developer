<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface OrdersRepository
{
    public function search(string $query = ""): Collection;
}
