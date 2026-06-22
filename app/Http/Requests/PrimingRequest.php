<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrimingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'volume_litros'     => 'required|numeric|min:0.1|max:10000',
            'temp_fermentacao'  => 'required|numeric|between:-5,35',
            'target_co2'        => 'required|numeric|between:0.5,5',
            'tipo_acucar'       => 'required|in:sucrose,dextrose_mono,dextrose_anidra,dme,mel',
            'volume_solucao_ml' => 'nullable|numeric|min:1|max:100000',
        ];
    }

    public function messages(): array
    {
        return [
            'volume_litros.required'    => 'Informe o volume de cerveja a envasar.',
            'volume_litros.min'         => 'O volume deve ser maior que zero.',
            'temp_fermentacao.required' => 'Informe a temperatura de fermentação/lagering.',
            'temp_fermentacao.between'  => 'A temperatura deve estar entre -5°C e 35°C.',
            'target_co2.required'       => 'Informe o volume de CO₂ desejado.',
            'target_co2.between'        => 'O volume de CO₂ deve estar entre 0,5 e 5 vols.',
            'tipo_acucar.required'      => 'Selecione o tipo de açúcar.',
            'tipo_acucar.in'            => 'Tipo de açúcar inválido.',
            'volume_solucao_ml.min'     => 'O volume da solução deve ser maior que zero.',
            '*.numeric'                 => 'O valor informado não é válido.',
        ];
    }
}
