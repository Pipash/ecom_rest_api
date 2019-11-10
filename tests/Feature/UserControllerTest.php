<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function testSignup()
    {
        $data = [
            'name'=> $this->faker->word,
            'email'=>$this->faker->email,
            'password'=>$this->faker->password,
            'is_admin'=>$this->faker->boolean,
            'shipping_address'=>$this->faker->word,
            'password_confirmation'=>$this->faker->password,
        ];
        $response = $this->post('api/signup', $data);

        $response->assertStatus(200);
    }

    public function testLogin()
    {
        $data = [
            'email'=>$this->faker->email,
            'password'=>$this->faker->password,
            'remember_me'=>$this->faker->boolean,
        ];
        $response = $this->post('api/signup', $data);

        $response->assertStatus(200);
    }
}
