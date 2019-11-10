<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /*protected $authentication = [
        'Content-Type'=>'application/json',
        'X-Requested-With'=>'XMLHttpRequest',
        'Authorization'=> 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiOWYwZDVkYTcxMTVkMjg0Y2QyMDZiNGEzZWMwYzE1NWMwOGM2Y2UzZjMzYTY2M2UzNDJjNWQ4MDczYmNlNTdkMjRjYzk0MTAzODgzZGZkZmIiLCJpYXQiOjE1NzM0MDQ5MjEsIm5iZiI6MTU3MzQwNDkyMSwiZXhwIjoxNjA1MDI3MzIxLCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.DpByPz4_D-Q-LZ7pV-D6rVw0DenchOqqM07Akvg0lgSNd9Oj6nziwEMBcd-B-ZjI5CoHKYRf44AZFE4kukaWp1_oz2yVOCs01vCy4oBYQCYmi-28izxSRUINrQKlKZbuA8W5qsLuHzCz994hzirw1w0w253auByS6BFzIRMKLQd8QAJVi6uP6FJbjc27KSS2DW5Us8WdjHrCTe_eu47yc5jk_4XFGyAT_0AxA1T73CQuISHuG4AweZ9rq3NAO3QCcSPS7Z4knl484CPq8MhZqhQ42lBKeMBGBvQCCcigznXXGq5Ak5OO8pCwhl3GOtuNz4ltd-lvqfUjRl2-vpv5vF_cGH9OjXz7u9zNahLQeloxifcOvhuuJjUtbLDOdxLQwn5jd77PSnONUFHFK4slo-8RjSnTixYd4PcOehH2gE-7M_LFyPNQY53klyZLep6lCFRgM2Qm9GdN4N4CF7sHw8cvuDqAZgwrvVTVVrvxf6MUDf4ce-d1ybf6pRtx9UUm42FcovQL7NdGRP8iHK0uPVr3lN5jwDDclbg-WXnqfTaKMLF2la2cupJGYk3k1OuJPKWmSXp3eoKmfvm_FH0mu64tEb-s--__ZpS7eiKBWSdqVr0TMMTST12BhijRn44FualC6bBj4HhpAg22_JvKl-H3RRbKPSJj6Cpq1LrqpeQ'
    ];*/

    public function testCreate()
    {
        $user = factory(\App\User::class)->make();
        $data = [
            'name'=> $this->faker->word,
            'price'=>$this->faker->numberBetween(1, 100),
            'description'=>$this->faker->word,
            'size'=>$this->faker->word,
            'color'=>$this->faker->word,
            'discount_percent'=>$this->faker->numberBetween(1,10),
            //children[0]:11
            //children[1]:12
            //children[2]:13
            //children[3]:4
            //is_bundle:1
        ];
        $response = $this->actingAs($user)->post('api/product/create', $data); //, $authentication

        $response
            ->assertJson([
                'status' => 'success',
                'message' => 'Successfully saved!'
            ]);
    }
}
