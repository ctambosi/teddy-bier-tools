<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TemperaturaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'celsius'    => 'nullable|numeric|between:-273,1000',
            'fahrenheit' => 'nullable|numeric|between:-459,2000',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $preenchidos = collect(['celsius', 'fahrenheit'])
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
        ];
    }
}
