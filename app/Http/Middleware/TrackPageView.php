<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Registra somente requisições GET de páginas Inertia (inicial ou navegação via XHR)
        if ($request->isMethod('GET') && !$request->is('up') && !$request->is('api/*')) {
            PageView::create([
                'page'       => $request->path(),
                'route_name' => $request->route()?->getName(),
                'created_at' => now(),
            ]);
        }

        return $response;
    }
}