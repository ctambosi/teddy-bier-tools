<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ebc'      => 'nullable|numeric|min:0',
            'srm'      => 'nullable|numeric|min:0',
            'lovibond' => 'nullable|numeric|min:0',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $preenchidos = collect(['ebc', 'srm', 'lovibond'])
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
            '*.numeric' => 'O valor informado não é válido.',
            '*.min'     => 'O valor não pode ser negativo.',
        ];
    }
}
