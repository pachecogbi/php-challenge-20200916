<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApiCheckerService;

class ApiCheckerController extends Controller
{
    private $apiCheckerService;

    public function __construct(ApiCheckerService $apiCheckerService) {
        $this->apiCheckerService = $apiCheckerService;
    }

    /**
     * Visualiza Status da Api
     *
     * Descreve os detalhes de bom funcionamento da API.
     * @group ApiChecker
     * @responseFile 200 Responses/ApiChecker/GetStatusApi/SuccessResponse.json
     * @responseFile 422 Responses/ApiChecker/GetStatusApi/UnprocessableResponse.json
     */
    public function getStatusApi()
    {
        try {
            $readWriteStatus = $this->apiCheckerService->checkDatabaseReadWrite();
            $lastCronCheck = $this->apiCheckerService->getLastCronCheckDate();
            $memoryAndUptime = $this->apiCheckerService->getMemoryAndUptime();

            return [
                'success' => true,
                'data' => [
                    'read_write_status' => $readWriteStatus,
                    'last_cron_check_date' => $lastCronCheck,
                    'used_memory' => $memoryAndUptime['memory'],
                    'online_time' => $memoryAndUptime['uptime'],
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error_message' => $e->getMessage(),
            ];
        }
    }
}
