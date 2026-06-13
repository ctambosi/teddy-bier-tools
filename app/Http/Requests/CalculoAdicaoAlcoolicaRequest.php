<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalculoAdicaoAlcoolicaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'volume_base'       => 'required|numeric|min:0.001',
            'graduacao_alcool'  => 'required|numeric|between:0.1,100',
            'graduacao_desejada'=> 'required|numeric|between:0.1,100',
            'unidade'           => 'required|in:ml,L',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $ga = (float) $this->input('graduacao_alcool', 0);
            $gd = (float) $this->input('graduacao_desejada', 0);
            if ($gd >= $ga) {
                $validator->errors()->add(
                    'graduacao_desejada',
                    'A graduação desejada deve ser menor que a graduação do álcool.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'volume_base.required'        => 'Informe o volume da base.',
            'volume_base.min'             => 'O volume deve ser maior que zero.',
            'graduacao_alcool.required'   => 'Informe a graduação do álcool.',
            'graduacao_alcool.between'    => 'A graduação deve estar entre 0,1% e 100%.',
            'graduacao_desejada.required' => 'Informe a graduação desejada.',
            'graduacao_desejada.between'  => 'A graduação deve estar entre 0,1% e 100%.',
            'unidade.required'            => 'Selecione a unidade de medida.',
            '*.numeric'                   => 'O valor informado não é válido.',
        ];
    }
}
