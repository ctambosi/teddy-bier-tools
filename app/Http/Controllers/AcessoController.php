<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AcessoService;
use Inertia\Inertia;
use Inertia\Response;

class AcessoController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Contador', [
            'total' => AcessoService::total(),
        ]);
    }
}
