<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = ['page', 'route_name', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}