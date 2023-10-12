<?php

namespace App\Console\Commands;

use App\Services\RequestProductsService;
use Illuminate\Console\Command;

class ImportDataProducts extends Command
{
    private $requestProductsService;

    public function __construct(RequestProductsService $requestProductsService)
    {
        $this->requestProductsService = $requestProductsService;
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is a command to import data made by the request into the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $products = explode("\n", $this->requestProductsService->requestData());
            $this->requestProductsService->addProducts($products);
            $this->requestProductsService->addFile();
            $this->requestProductsService->createLog('Importing products into the database');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->requestProductsService->createLog($e->getMessage());
        }
    }
}
