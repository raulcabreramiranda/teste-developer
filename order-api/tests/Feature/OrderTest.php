<?php
namespace Tests;

use Tests\TestCase;
use App\Order;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{

    public function setUp(): void{
        parent::setUp();

        $this->artisan('migrate');
        $this->artisan('db:seed');
    }

    public function tearDown(): void
    {
        $this->artisan('migrate:reset');
        parent::tearDown();
    }

    /**
     * Chamada a factory para gerar um usuario
     *
     * @return class
     */
    public function createOrder()
    {
        return factory(Order::class)->create();
    }

    /**
     * GET /orders/
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->json('GET', "/api/orders");
        $response->assertStatus(200);


        $response = $this->json('GET', "/api/orders?page=1&limit=10");
        $response->assertStatus(200)->assertJsonFragment([
            "page"=>1,
            "limit"=>10,
        ]);
    }

    /**
     * GET /orders/<id>
     *
     * @return void
     */
    public function testShow()
    {
        // Create a test shop with filled out fields
        $activity = $this->createOrder();
        // Check the API for the new entry
        $response = $this->json('GET', "api/orders/{$activity->id}");
        // Delete the test shop
        $activity->delete();
        $response->assertStatus(200);
    }

    /**
     * POST /orders/
     *
     * @return void
     */
    public function testStore()
    {
        $activity = [
            "user_id"=> 1,
            "item_description"=> "Necessitatibus ab exercitationem dolor.",
            "item_quantity"=> 5,
            "item_price"=> "114.54"
        ];

        $response = $this->json('POST', "api/orders/", $activity);

        $response
            ->assertStatus(201)
            ->assertJsonFragment($activity);

        unset($activity['user_id']);
        $response = $this->json('POST', "api/orders/", $activity);
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "message" => "Falha na validação","errors"=>array("The user id field is required.")
            ]);
    }

    /**
     * DELETE /orders/<id>
     * Teste do método destroy() que exclui um usuario
     *
     * @return void
     */
    public function testDestroy()
    {
        $activity = $this->createOrder();
        $response = $this->json('DELETE', "api/orders/{$activity->id}");
        $response
            ->assertStatus(204);
    }
}
