<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Acesso extends Model
{
    public $timestamps = false;

    protected $fillable = ['ip', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
