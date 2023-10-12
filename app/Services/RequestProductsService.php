<?php

namespace App\Services;

use App\Repositories\FileRepository;
use App\Repositories\LogRepository;
use App\Repositories\ProductRepository;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RequestProductsService
{
    private $productRepository;
    private $fileRepository;
    private $logRepository;
    private $httpClient;

    public function __construct(ProductRepository $productRepository, FileRepository $fileRepository, LogRepository $logRepository, Client $httpClient)
    {
        $this->productRepository = $productRepository;
        $this->fileRepository = $fileRepository;
        $this->logRepository = $logRepository;
        $this->httpClient = $httpClient;
    }

    public function requestData()
    {
        $response = Http::get(config('app.request_data_url'));

        if (!$response->successful()) {
            throw new Exception('Failed to retrieve data from remote server');
        }

        return $response->body();
    }

    public function createLog($message)
    {
        return DB::transaction(function () use ($message) {
            return $this->logRepository->createLog([
                'message' => $message,
                'log_date' => Carbon::now()->subHours(3)->format('Y/m/d H:i:s')
            ]);
        });
    }

    public function addProducts($products)
    {
        $existingFiles = $this->fileRepository->getAllFiles()->pluck('name')->toArray();
        $dataProducts = array_diff($products, $existingFiles);

        if (empty($dataProducts)) {
            return [];
        }

        array_pop($dataProducts);

        return $this->fileRepository->createFiles($dataProducts);
    }

    public function getFileFromServer($path, $productName)
    {
        $response = $this->httpClient->request('GET', config('remote.request_base_data_url') . "/$productName", [
            'sink' => $path . $productName
        ]);

        if ($response->getStatusCode() === 200) {
            return true;
        }
    }

    private function writeFile($path, $product)
    {
        $gzippedFilePath = $path . $product->name;
        $outputFilePath = $path . 'arquivoExtraido.json';

        $content = [];

        if (($gz = gzopen($gzippedFilePath, 'r')) !== false) {
            if (($outputFile = fopen($outputFilePath, 'w')) !== false) {
                while (($string = gzread($gz, 4096)) !== false) {
                    fwrite($outputFile, $string, strlen($string));
                }

                fclose($outputFile);
            }

            gzclose($gz);

            if (($inputFile = fopen($outputFilePath, 'rb')) !== false) {
                for ($i = 0; $i < 100; $i++) {
                    $line = fgets($inputFile);
                    if ($line === false) {
                        break;
                    }
                    $content[] = json_decode($line);
                }

                fclose($inputFile);
            }
        }

        return $content;
    }

    private function saveData($content)
    {
        DB::transaction(function () use ($content) {
            foreach ($content as $newProduct) {
                $this->productRepository->addProduct($this->mountData($newProduct));
            }
        });
    }

    private function mountData($product)
    {
        $fieldsToCopy = [
            "code", "url", "creator", "created_t", "last_modified_t", "product_name",
            "quantity", "brands", "categories", "labels", "cities", "purchase_places",
            "stores", "ingredients_text", "traces", "serving_size", "serving_quantity",
            "nutriscore_score", "nutriscore_grade", "main_category", "image_url"
        ];

        $data = ["status" => "published"];

        foreach ($fieldsToCopy as $field) {
            $data[$field] = $product->$field;
        }

        $data["imported_t"] = Carbon::now()->subHours(3)->format('Y/m/d H:i:s');

        return $data;
    }

    private function updateProduct($product)
    {
        DB::transaction(function () use ($product) {
            $this->productRepository->turnRunToFalse($product);
        });
    }

    private function deleteFile($product)
    {
        $productFilePath = public_path('storage/' . $product->name);
        $extraFilePath = public_path('storage/arquivoExtraido.json');

        try {
            if (file_exists($productFilePath)) {
                unlink($productFilePath);
            }

            if (file_exists($extraFilePath)) {
                unlink($extraFilePath);
            }
        } catch (Exception $e) {
            Log::error("An exception occurred while processing the file: " . $e->getMessage());
        }
    }

    public function addFile()
    {
        $storagePath = config('local.storage_path');

        $fileRepository = $this->fileRepository->getAllFiles();

        $fileRepository->each(function ($file) use ($storagePath) {
            if ($file->run === 0) {
                return;
            }

            try {
                $this->getFileFromServer($storagePath, $file);
                $content = $this->writeFile($storagePath, $file);
                $this->saveData($content);
                $this->updateProduct($file);
                $this->deleteFile($file);
            } catch (Exception $e) {
                Log::error("An exception occurred while processing the file: " . $e->getMessage());
            }
        });
    }
}
