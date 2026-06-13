<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CalculoLeveduraRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'celulas'      => 'required|numeric|min:1',
            'concentracao' => 'required|numeric|min:0.1|max:10',
        ];
    }
    public function messages(): array
    {
        return [
            'celulas.required'      => 'Informe a quantidade de bilhões de células.',
            'celulas.min'           => 'A quantidade de células deve ser pelo menos 1.',
            'concentracao.required' => 'Informe a concentração da lama.',
            'concentracao.min'      => 'A concentração deve ser maior que zero.',
            '*.numeric'             => 'O valor informado não é válido.',
        ];
    }
}
