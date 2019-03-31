<?php
use App\Order;
use Illuminate\Database\Seeder;

class OrderSeed extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('orders')->truncate();
        factory(Order::class, 50)->create();
    }
}
