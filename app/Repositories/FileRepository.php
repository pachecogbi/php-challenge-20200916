<?php

namespace App\Repositories;

use App\Models\File;

class FileRepository
{

    public function getAllFiles()
    {
        return File::all();
    }

    public function createFiles($product)
    {
        foreach ($product as $productName) {
            File::create(['name' => $productName]);
        };

        return true;
    }
}
