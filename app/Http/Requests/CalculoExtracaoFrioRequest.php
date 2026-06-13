<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CalculoExtracaoFrioRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'gramas' => 'required|numeric|min:1|max:100000',
        ];
    }
    public function messages(): array
    {
        return [
            'gramas.required' => 'Informe a quantidade de malte em gramas.',
            'gramas.min'      => 'A quantidade deve ser pelo menos 1 grama.',
            'gramas.numeric'  => 'O valor informado não é válido.',
        ];
    }
}
