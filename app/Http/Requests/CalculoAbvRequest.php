<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CalculoAbvRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'og' => 'required|numeric|between:0.8,1.4',
            'fg' => 'required|numeric|between:0.8,1.4',
        ];
    }
    public function messages(): array
    {
        return [
            'og.required' => 'Informe a densidade original (OG).',
            'og.between'  => 'A OG deve estar entre 0,800 e 1,400.',
            'fg.required' => 'Informe a densidade final (FG).',
            'fg.between'  => 'A FG deve estar entre 0,800 e 1,400.',
            '*.numeric'   => 'O valor informado não é válido.',
        ];
    }
}
