<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CalculoPercentualAcucarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'og'         => 'required|numeric|between:0.8,1.4',
            'percentual' => 'required|numeric|between:0.1,100',
        ];
    }
    public function messages(): array
    {
        return [
            'og.required'         => 'Informe a densidade desejada (OG).',
            'og.between'          => 'A OG deve estar entre 0,800 e 1,400.',
            'percentual.required' => 'Informe o percentual de açúcar.',
            'percentual.between'  => 'O percentual deve estar entre 0,1% e 100%.',
            '*.numeric'           => 'O valor informado não é válido.',
        ];
    }
}
