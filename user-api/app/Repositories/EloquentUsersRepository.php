<?php

namespace App\Repositories;

use App\User;
use Illuminate\Database\Eloquent\Collection;

class EloquentUsersRepository implements UsersRepository
{
    public function search(string $query = ""): Collection
    {
        return User::where('name', 'like', "%{$query}%")
        ->orWhere('email', 'like', "%{$query}%")
        ->orWhere('cpf', 'like', "%{$query}%")
            ->get();
    }
    public function searchId(string $query = ""): Collection
    {
        return User::select('id')->where('name', 'like', "%{$query}%")
        ->orWhere('email', 'like', "%{$query}%")
        ->orWhere('cpf', 'like', "%{$query}%")
            ->get();
    }
}
