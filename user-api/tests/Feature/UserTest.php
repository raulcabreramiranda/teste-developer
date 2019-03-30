<?php
namespace Tests;

use Tests\TestCase;
use App\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
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
    public function createUser()
    {
        return factory(User::class)->create();
    }

    /**
     * GET /users/
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->json('GET', "/api/users");
        $response->assertStatus(200);


        $response = $this->json('GET', "/api/users?page=1&limit=10");
        $response->assertStatus(200)->assertJsonFragment([
            "page"=>'1',
            "limit"=>'10',
        ]);
    }

    /**
     * GET /users/<id>
     *
     * @return void
     */
    public function testShow()
    {
        // Create a test shop with filled out fields
        $activity = $this->createUser();
        // Check the API for the new entry
        $response = $this->json('GET', "api/users/{$activity->id}");
        // Delete the test shop
        $activity->delete();
        $response->assertStatus(200);
    }

    /**
     * POST /users/
     *
     * @return void
     */
    public function testStore()
    {
        $activity = [
            "name" => "Trudie Gerlach_1",
            "cpf" => "000.000.000-00",
            "email" => "caltenwerth@example.net_1",
            "phone_number" => "(00) 000-0000",
        ];

        $response = $this->json('POST', "api/users/", $activity);

        $response
            ->assertStatus(201)
            ->assertJsonFragment($activity);

        $response = $this->json('POST', "api/users/", $activity);
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "message" => "Falha na validação","errors"=>array("The email has already been taken.")
            ]);

        unset($activity['name']);
        $response = $this->json('POST', "api/users/", $activity);
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "message" => "Falha na validação","errors"=>array("The name field is required.","The email has already been taken.")
            ]);
    }

    /**
     * DELETE /users/<id>
     * Teste do método destroy() que exclui um usuario
     *
     * @return void
     */
    public function testDestroy()
    {
        $activity = $this->createUser();
        $response = $this->json('DELETE', "api/users/{$activity->id}");
        $response
            ->assertStatus(204);
    }
}
