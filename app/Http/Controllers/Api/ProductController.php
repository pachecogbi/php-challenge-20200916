<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    private $productRepository;

    public function __construct()
    {
        $this->productRepository = new ProductRepository();
    }

    /**
     * Listagem de Produtos
     *
     * Lista todos os produtos já inseridos no banco de dados.
     * @group Products
     * @responseFile 200 Responses/Products/Index/SuccessResponse.json
     */
    public function index()
    {
        try {
            return $this->productRepository->getAllProducts()->paginate(20);
        } catch (\Throwable $th) {
            $errorMessage = $th->getMessage();
            $statusCode = 422;

            return response(['error' => $errorMessage], $statusCode);
        }
    }

    /**
     * Visualizar produto
     *
     * Visualiza o produto com base no code.
     * @group Products
     * @urlParam code integer required code.
     * @responseFile 200 Responses/Products/Show/SuccessResponse.json
     * @responseFile 422 Responses/Products/Show/UnprocessableResponse.json
     * @response 404 {"message": "No query results for model [App\\Models\\Product]."}
     */
    public function show($code)
    {
        try {
            return $this->productRepository->getProductByCode($code)->firstOrFail();
        } catch (\Throwable $th) {
            $errorMessage = $th->getMessage();
            $statusCode = 422;

            if ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $statusCode = 404;
            }

            return response(['error' => $errorMessage], $statusCode);
        }
    }

    /**
     * Atualizar produto
     *
     * Atualiza o produto com base no code.
     * @group Products
     * @urlParam code integer required code.
     * @responseFile 200 Responses/Products/Update/SuccessResponse.json
     * @responseFile 422 Responses/Products/Update/UnprocessableResponse.json
     * @response 404 {"message": "No query results for model [App\\Models\\Product]."}
     */
    public function update($code, Request $request)
    {
        try {
            DB::transaction(function () use ($code, $request) {
                $product = $this->productRepository->getProductByCode($code)->firstOrFail();
                return $product->update($request->all());
            });

            return response(["message" => "The product with code " . $code .  " has been updated successfully."]);
        } catch (\Throwable $th) {
            $errorMessage = $th->getMessage();
            $statusCode = 422;

            if ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $statusCode = 404;
            }

            return response(['error' => $errorMessage], $statusCode);
        }
    }

    /**
     * Altera status produto
     *
     * Altera o status do produto com base no code para trash.
     * @group Products
     * @urlParam code integer required code.
     * @responseFile 200 Responses/Products/Delete/SuccessResponse.json
     * @responseFile 422 Responses/Products/Delete/UnprocessableResponse.json
     * @response 404 {"message": "No query results for model [App\\Models\\Product]."}
     */
    public function delete($code)
    {
        try {
            DB::transaction(function () use ($code) {
                $product = $this->productRepository->getProductByCode($code)->firstOrFail();
                return $product->update([
                    'status' => 'trash'
                ]);
            });

            return response(["message" => "The code " . $code . " product had its status changed to trash"]);
        } catch (\Throwable $th) {
            $errorMessage = $th->getMessage();
            $statusCode = 422;

            if ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $statusCode = 404;
            }

            return response(['error' => $errorMessage], $statusCode);
        }
    }
}
