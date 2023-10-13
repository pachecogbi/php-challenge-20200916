<?php

namespace App\Repositories;

use App\Models\File;
use Illuminate\Support\Facades\DB;

class FileRepository
{

    public function getAllFiles()
    {
        return File::all();
    }

    public function createFiles($params)
    {
        try {
            DB::transaction(function () use ($params) {
                foreach ($params as $fileName) {
                    if (!empty($fileName)) {
                        File::create(['file_name' => $fileName]);
                    }
                };
            });

            return true;
        } catch (\Throwable $th) {
            return response(["error: " => $th->getMessage()]);
        }
    }
}
