<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VolumeMostoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'diametro'       => 'required|numeric|min:1|max:500',
            'altura_liquido' => 'required|numeric|min:0.1|max:1000',
            'perda'          => 'nullable|numeric|min:0|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'diametro.required'      => 'Informe o diâmetro da panela.',
            'diametro.min'           => 'O diâmetro deve ser maior que zero.',
            'altura_liquido.required' => 'Informe a altura do líquido na panela.',
            'altura_liquido.min'     => 'A altura deve ser maior que zero.',
            '*.numeric'              => 'O valor informado não é válido.',
        ];
    }
}
