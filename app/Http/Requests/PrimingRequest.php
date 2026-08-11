<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrimingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'modo'        => 'required|in:co2,manual',
            'tipo_acucar' => 'required|in:sucrose,dextrose_mono,dextrose_anidra,dme,mel',

            'volume_litros' => 'nullable|numeric|min:0.1|max:10000',

            'temp_fermentacao' => 'required_if:modo,co2|numeric|between:-5,35',
            'target_co2'       => 'required_if:modo,co2|numeric|between:0.5,5',
            'gramas_por_litro' => 'required_if:modo,manual|numeric|min:0.1|max:20',

            'volume_solucao_inicial_ml' => 'nullable|numeric|min:1|max:100000|required_with:volume_solucao_final_ml',
            'volume_solucao_final_ml'   => 'nullable|numeric|min:1|max:100000|required_with:volume_solucao_inicial_ml|lte:volume_solucao_inicial_ml',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_acucar.required'          => 'Selecione o tipo de açúcar.',
            'tipo_acucar.in'                => 'Tipo de açúcar inválido.',
            'modo.required'                 => 'Selecione como deseja calcular a dosagem.',
            'volume_litros.min'             => 'O volume deve ser maior que zero.',
            'temp_fermentacao.required_if'  => 'Informe a temperatura de fermentação/lagering.',
            'temp_fermentacao.between'      => 'A temperatura deve estar entre -5°C e 35°C.',
            'target_co2.required_if'        => 'Informe o volume de CO₂ desejado.',
            'target_co2.between'            => 'O volume de CO₂ deve estar entre 0,5 e 5 vols.',
            'gramas_por_litro.required_if'  => 'Informe quantos gramas de açúcar por litro deseja usar.',
            'volume_solucao_inicial_ml.required_with' => 'Informe também o volume da solução antes de ferver.',
            'volume_solucao_inicial_ml.min'  => 'O volume deve ser maior que zero.',
            'volume_solucao_final_ml.required_with'   => 'Informe também o volume da solução depois de ferver.',
            'volume_solucao_final_ml.min'     => 'O volume deve ser maior que zero.',
            'volume_solucao_final_ml.lte'     => 'O volume depois de ferver não pode ser maior que o volume antes de ferver — a fervura só evapora água.',
            '*.numeric'                     => 'O valor informado não é válido.',
        ];
    }
}
