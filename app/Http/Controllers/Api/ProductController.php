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

    public function index()
    {
        try {
            return $this->productRepository->getAllProducts()->paginate(20);
        } catch (\Throwable $th) {
            $errorMessage = $th->getMessage();
            $statusCode = 500;

            if ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $statusCode = 404;
            }

            return response(['error' => $errorMessage], $statusCode);
        }
    }

    public function show($code)
    {
        try {
            return $this->productRepository->getProductByCode($code)->firstOrFail();
        } catch (\Throwable $th) {
            $errorMessage = $th->getMessage();
            $statusCode = 500;

            if ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $statusCode = 404;
            }

            return response(['error' => $errorMessage], $statusCode);
        }
    }

    public function update($code, Request $request)
    {
        try {
            $response = DB::transaction(function() use ($code, $request) {
                $product = $this->productRepository->getProductByCode($code)->firstOrFail();
                return $product->update($request->all());
            });

            return $response;
        } catch (\Throwable $th) {
            $errorMessage = $th->getMessage();
            $statusCode = 500;

            if ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $statusCode = 404;
            }

            return response(['error' => $errorMessage], $statusCode);
        }
    }

    public function delete($code)
    {
        try {
            $response = DB::transaction(function() use($code) {
                $product = $this->productRepository->getProductByCode($code)->firstOrFail();
                return $product->update([
                    'status' => 'trash'
                ]);
            });

            return $response;
        } catch (\Throwable $th) {
            $errorMessage = $th->getMessage();
            $statusCode = 500;

            if ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $statusCode = 404;
            }

            return response(['error' => $errorMessage], $statusCode);
        }
    }
}
