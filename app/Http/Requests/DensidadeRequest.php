<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DensidadeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sg'    => 'nullable|numeric|between:0.8,1.4',
            'brix'  => 'nullable|numeric|between:0,100',
            'plato' => 'nullable|numeric|between:0,80',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $preenchidos = collect(['sg', 'brix', 'plato'])
                ->filter(fn($f) => $this->filled($f))
                ->count();

            if ($preenchidos === 0) {
                $validator->errors()->add('geral', 'Preencha um dos campos para calcular.');
            } elseif ($preenchidos > 1) {
                $validator->errors()->add('geral', 'Preencha apenas um campo por vez.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'sg.between'    => 'A densidade SG deve estar entre 0,800 e 1,400.',
            'brix.between'  => 'O Brix deve estar entre 0 e 100.',
            'plato.between' => 'O Plato deve estar entre 0 e 80.',
            '*.numeric'     => 'O valor informado não é válido.',
        ];
    }
}
