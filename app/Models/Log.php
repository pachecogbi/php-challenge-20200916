<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'message',
        'log_date'
    ];

    protected $casts = [
        'log_date' => 'datetime'
    ];

    public $timestamps = false;
}
