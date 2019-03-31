<?php

use App\Order;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Order::class, function (Faker $faker) {
    $itemQuantity = $faker->randomDigitNotNull();
    $itemPrice = $faker->randomFloat(2,1,200);
    return [
        'user_id' => $faker->numberBetween(1,50),
        'item_description' => $faker->sentence(),
        'item_quantity' => $itemQuantity,
        'item_price' => $itemPrice,
        'total_value' => $itemPrice * $itemQuantity
    ];
});
