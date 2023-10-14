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
    private $storagePath;

    public function __construct(ProductRepository $productRepository, FileRepository $fileRepository, LogRepository $logRepository, Client $httpClient)
    {
        $this->productRepository = $productRepository;
        $this->fileRepository = $fileRepository;
        $this->logRepository = $logRepository;
        $this->httpClient = $httpClient;
        $this->storagePath = storage_path('product_files/');
    }

    public function requestFileName()
    {
        $response = Http::get('https://challenges.coode.sh/food/data/json/index.txt');

        if (!$response->successful()) {
            throw new Exception('Failed to retrieve data from remote server');
        }

        return $response->body();
    }

    public function createLog($message)
    {
        try {
            return DB::transaction(function () use ($message) {
                $this->logRepository->createLog([
                    'message' => $message,
                    'log_date' => Carbon::now()->subHours(3)->format('Y/m/d H:i:s')
                ]);
            });
        } catch (\Throwable $th) {
            return response(["error: " => $th->getMessage()]);
        }
    }

    public function insertFiles($files)
    {
        $existingFiles = $this->fileRepository->getAllFiles()->pluck('file_name')->toArray();

        $dataFiles = array_diff($files, $existingFiles);

        if (empty($dataFiles)) {
            return [];
        }

        return $this->fileRepository->createFiles($dataFiles);
    }

    public function getFileFromServer($productName)
    {
        $response = $this->httpClient->request('GET', "https://challenges.coode.sh/food/data/json" . "/$productName", [
            'sink' => $this->storagePath . $productName
        ]);

        if ($response->getStatusCode() === 200) {
            return true;
        }
    }

    private function writeFile($product)
    {
        $gz = gzopen($this->storagePath . $product->file_name, 'r');
        $fp = fopen($this->storagePath . "arquivoExtraido.json", 'w');
        while ($string = gzread($gz, 4096)) {
            fwrite($fp, $string, strlen($string));
        }
        fclose($fp);
        gzclose($gz);

        $fh = fopen($this->storagePath . 'arquivoExtraido.json', 'rb');
        $content = [];
        for ($i = 0; $i < 100; $i++) {
            $line = fgets($fh);
            if ($line !== false) {
                $content[] = json_decode($line);
            }
        }
        fclose($fh);

        return $content;
    }

    private function saveDataProducts($content)
    {
        try {
            foreach ($content as $newProduct) {
                DB::transaction(function () use ($newProduct) {
                    $this->productRepository->addProduct($this->mountData($newProduct));
                });
            }
        } catch (\Throwable $th) {
            return response(["error: " => $th->getMessage()]);
        }
    }

    private function mountData($product)
    {
        $fieldsToCopy = [
            "url", "creator", "created_t", "last_modified_t", "product_name",
            "quantity", "brands", "categories", "labels", "cities", "purchase_places",
            "stores", "ingredients_text", "traces", "serving_size", "serving_quantity",
            "nutriscore_score", "nutriscore_grade", "main_category", "image_url"
        ];

        $data = [
            "code" => str_replace('"', "", $product->code),
            "status" => "published",
            "imported_t" => Carbon::now()->subHours(3)->format('Y/m/d H:i:s')
        ];

        foreach ($fieldsToCopy as $field) {
            $data[$field] = $product->$field === "" ? null : $product->$field;
        }

        return $data;
    }

    private function updateFile($product)
    {
        DB::transaction(function () use ($product) {
            return $product->update(['will_run' => 0]);
        });
    }

    private function deleteFile($fileName)
    {
        $productFilePath = $this->storagePath . $fileName;
        $extraFilePath = $this->storagePath . "arquivoExtraido.json";

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

    public function processDataOperations()
    {
        $this->fileRepository->getAllFiles()->each(function ($file) {
            if ($file->will_run === 0) {
                return;
            }

            try {
                $this->getFileFromServer($file->file_name);
                $content = $this->writeFile($file);
                $this->saveDataProducts($content);
                $this->updateFile($file);
                $this->deleteFile($file->file_name);
            } catch (Exception $e) {
                Log::error("An exception occurred while processing the file: " . $e->getMessage());
            }
        });
    }
}
