<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PressaoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'bar' => 'nullable|numeric|min:0',
            'psi' => 'nullable|numeric|min:0',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $preenchidos = collect(['bar', 'psi'])
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
