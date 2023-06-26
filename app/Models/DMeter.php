<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DMeter extends Model
{
    use HasFactory;

    protected $fillable = [
        'datatime',
        'power',
        'power2',
        'energy',
        'energy2'
    ];
}
