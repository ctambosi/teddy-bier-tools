<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefratometroRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'og'           => 'required|numeric|between:0.8,1.4',
            'leitura_atual' => 'required|numeric|between:0.8,1.4',
        ];
    }

    public function messages(): array
    {
        return [
            'og.required'            => 'Informe a densidade original (OG).',
            'og.between'             => 'A OG deve estar entre 0,800 e 1,400.',
            'leitura_atual.required' => 'Informe a leitura atual do refratômetro.',
            'leitura_atual.between'  => 'A leitura deve estar entre 0,800 e 1,400.',
            '*.numeric'              => 'O valor informado não é válido.',
        ];
    }
}
