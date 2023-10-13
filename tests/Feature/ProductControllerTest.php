<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\ProductController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{

    public function test_CaseIndexReturnValues()
    {
        $response = $this->json('GET', '/api/products/');
        dd($response);
        $response->assertStatus(200);
    }

    /**
     * @depends test_CaseIndexReturnValues
     */
    public function test_CaseProductIsVisualized($params)
    {

        $response = $this->json('GET', '/api/products/' . $params['product_code']);

        $response->assertStatus(200);
        return ['product' => json_decode($response->content())];
    }


    /**
     * @depends test_CaseProductIsVisualized
     */
    public function test_CaseAProductIsUpdatedSuccessfully($params)
    {

        $response = $this->json('PUT', '/api/products/' . $params['product_code'], [
            'creator' => 'tester'
        ]);

        $response->assertStatus(200);
    }
}
