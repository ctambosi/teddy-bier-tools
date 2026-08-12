<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrecaoMostoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'volume'              => 'required|numeric|min:0.001|max:10000',
            'densidade_atual'     => 'required|numeric|between:1.001,1.4',
            'densidade_desejada'  => 'required|numeric|between:1.001,1.4',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $atual    = (float) $this->input('densidade_atual', 0);
            $desejada = (float) $this->input('densidade_desejada', 0);
            if ($atual === $desejada) {
                $validator->errors()->add(
                    'geral',
                    'A densidade atual já é igual à desejada — nenhuma correção é necessária.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'volume.required'             => 'Informe o volume do mosto.',
            'volume.min'                  => 'O volume deve ser maior que zero.',
            'densidade_atual.required'    => 'Informe a densidade atual do mosto.',
            'densidade_atual.between'     => 'A densidade atual deve estar entre 1,001 e 1,400.',
            'densidade_desejada.required' => 'Informe a densidade desejada.',
            'densidade_desejada.between'  => 'A densidade desejada deve estar entre 1,001 e 1,400.',
            '*.numeric'                   => 'O valor informado não é válido.',
        ];
    }
}
