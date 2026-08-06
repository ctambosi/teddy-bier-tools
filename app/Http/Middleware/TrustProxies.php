<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    protected $proxies = '*';

    protected $headers = [
        'FORWARDED' => 'FORWARDED',
        'X_FORWARDED_FOR' => 'X_FORWARDED_FOR',
        'X_FORWARDED_HOST' => 'X_FORWARDED_HOST',
        'X_FORWARDED_PROTO' => 'X_FORWARDED_PROTO',
        'X_FORWARDED_AWS_ELB' => 'X_FORWARDED_AWS_ELB',
    ];
}
