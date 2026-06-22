<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PressaoTemperaturaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'volume_co2'  => 'required|numeric|between:0.5,5',
            'temperatura' => 'required|numeric|between:-5,50',
        ];
    }

    public function messages(): array
    {
        return [
            'volume_co2.required'  => 'Informe o volume de CO₂ desejado.',
            'volume_co2.between'   => 'O volume de CO₂ deve estar entre 0,5 e 5,0 vols.',
            'temperatura.required' => 'Informe a temperatura da cerveja.',
            'temperatura.between'  => 'A temperatura deve estar entre -5 °C e 50 °C.',
            '*.numeric'            => 'O valor informado não é válido.',
        ];
    }
}
