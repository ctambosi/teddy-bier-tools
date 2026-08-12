<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Acesso;
use Illuminate\Support\Carbon;

class AcessoService
{
    private const INTERVALO_MINUTOS = 15;

    public static function registrar(string $ip): void
    {
        $acessoRecente = Acesso::where('ip', $ip)
            ->where('created_at', '>=', Carbon::now()->subMinutes(self::INTERVALO_MINUTOS))
            ->exists();

        if ($acessoRecente) {
            return;
        }

        Acesso::create([
            'ip' => $ip,
            'created_at' => Carbon::now(),
        ]);
    }

    public static function total(): int
    {
        return Acesso::count();
    }
}
