<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MotorRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'n1'         => 'nullable|numeric|min:1|max:100000',
            'd1'         => 'nullable|numeric|min:1|max:10000',
            'n2'         => 'nullable|numeric|min:1|max:100000',
            'd2'         => 'nullable|numeric|min:1|max:10000',
            'unidade_d1' => 'required|in:mm,cm',
            'unidade_d2' => 'required|in:mm,cm',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $campos = ['n1', 'd1', 'n2', 'd2'];
            $vazios = array_filter($campos, fn($c) => blank($this->input($c)));

            if (count($vazios) === 0) {
                $validator->errors()->add('geral', 'Deixe exatamente um dos quatro campos em branco para ser calculado.');
            } elseif (count($vazios) > 1) {
                $validator->errors()->add('geral', 'Preencha três dos quatro campos. Apenas um pode ficar em branco.');
            }
        });
    }

    public function messages(): array
    {
        return [
            '*.numeric' => 'O valor informado não é válido.',
            '*.min'     => 'O valor deve ser maior que zero.',
        ];
    }
}
