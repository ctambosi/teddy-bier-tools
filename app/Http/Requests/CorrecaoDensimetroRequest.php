<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrecaoDensimetroRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sg'         => 'required|numeric|between:0.8,1.4',
            'temperatura' => 'required|numeric|between:-10,100',
            'calibracao'  => 'required|in:15,20',
        ];
    }

    public function messages(): array
    {
        return [
            'sg.required'          => 'Informe a densidade medida.',
            'sg.between'           => 'A densidade SG deve estar entre 0,800 e 1,400.',
            'temperatura.required' => 'Informe a temperatura da amostra.',
            'temperatura.between'  => 'A temperatura deve estar entre -10 °C e 100 °C.',
            'calibracao.required'  => 'Selecione a temperatura de calibração do densímetro.',
            'calibracao.in'        => 'Temperatura de calibração inválida.',
            '*.numeric'            => 'O valor informado não é válido.',
        ];
    }
}
