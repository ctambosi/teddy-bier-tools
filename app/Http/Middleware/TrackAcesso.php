<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AcessoService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAcesso
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Registra o acesso (com dedup por IP a cada 15min), exceto na própria página do contador
        $ip = $request->ip();
        if (
            $ip !== null
            && $request->isMethod('GET')
            && !$request->is('up')
            && !$request->is('api/*')
            && $request->route()?->getName() !== 'contador'
        ) {
            AcessoService::registrar($ip);
        }

        return $response;
    }
}
