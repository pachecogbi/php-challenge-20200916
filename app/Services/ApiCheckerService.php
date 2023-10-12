<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Support\Facades\DB;

class ApiCheckerService
{

    public function checkDatabaseReadWrite()
    {
        try {
            DB::transaction(function () {
                $readResult = DB::selectOne("SELECT 1+1 as result");
                if ($readResult->result != 2) {
                    throw new \Exception("Failed to read the database");
                }

                $productData = ['code' => 1];
                $productRepository = DB::table('products');
                $productRepository->insert($productData);
                $writeResult = DB::selectOne("SELECT code FROM products WHERE code = ?", [$productData['code']]);
                if (empty($writeResult)) {
                    throw new \Exception("Failed to write to the database");
                }

                $productRepository->where('code', $productData['code'])->delete();
            });

            return "Success. The database check was completed successfully.";
        } catch (\Exception $e) {
            DB::rollBack();
            return "Database check failed: " . $e->getMessage();
        }
    }

    public function getLastCronCheckDate()
    {
        try {
            return Log::orderBy('id', 'desc')->value('log_date');
        } catch (\Exception $e) {
            return "Cron log check failed: " . $e->getMessage();
        }
    }

    public function getMemoryAndUptime()
    {
        try {
            $uptime = DB::selectOne("SHOW GLOBAL STATUS LIKE 'Uptime'")->Value;
            $memory = DB::selectOne("SHOW GLOBAL STATUS LIKE 'Bytes_received'")->Value;

            $uptime = number_format((int) $uptime / 60, 2, '.') . ' min';
            $memory = number_format((int) $memory / 1e+6, 2, '.') . ' MB';

            return ['uptime' => $uptime, 'memory' => $memory];
        } catch (\Exception $e) {
            return "Unable to access the data: " . $e->getMessage();
        }
    }
}
