<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    /**
     * Test if a product is created successfully.
     */
    public function test_CaseProductIsCreatedSuccessfully()
    {
        $response = $this->json('POST', '/api/products/', [
            'code' => '01',
            'creator' => 'tester'
        ]);

        $response->assertStatus(201);
        $productData = json_decode($response->content());

        return $productData->code;
    }

    /**
     * Test if a product is displayed successfully.
     *
     * @depends test_CaseProductIsCreatedSuccessfully
     */
    public function test_CaseProductIsDisplayedSuccessfully($productCode)
    {
        $response = $this->json('GET', '/api/products/' . $productCode);

        $response->assertStatus(200);
        $product = json_decode($response->content());

        $this->assertEquals($productCode, $product->code);
    }

    /**
     * Test if a product is updated successfully.
     *
     * @depends test_CaseProductIsCreatedSuccessfully
     */
    public function test_CaseProductIsUpdatedSuccessfully($productCode)
    {
        $response = $this->json('PUT', '/api/products/' . $productCode, [
            'creator' => 'tester'
        ]);

        $response->assertStatus(200);
    }
}
