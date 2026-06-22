<?php

declare(strict_types=1);

namespace App\Services;

class CorService
{
    public static function calcularCor(array $input): array
    {
        if ($input['ebc'] !== null) {
            $srm = $input['ebc'] * 0.508;
            return [
                'ebc'             => $input['ebc'],
                'srm'             => $srm,
                'lovibond'        => ($srm + 0.76) / 1.3546,
                'campo_informado' => 'ebc',
            ];
        }

        if ($input['srm'] !== null) {
            $srm = $input['srm'];
            return [
                'ebc'             => $srm * 1.97,
                'srm'             => $srm,
                'lovibond'        => ($srm + 0.76) / 1.3546,
                'campo_informado' => 'srm',
            ];
        }

        $srm = 1.3546 * $input['lovibond'] - 0.76;
        return [
            'ebc'             => $srm * 1.97,
            'srm'             => $srm,
            'lovibond'        => $input['lovibond'],
            'campo_informado' => 'lovibond',
        ];
    }
}