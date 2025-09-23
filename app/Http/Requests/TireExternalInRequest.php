<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TireExternalInRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }
    public function rules(): array
    {
        return [
            'tire_id' => ['required','exists:tires,id'],
            // posição agora flexível (validaremos existência real no serviço)
            'position' => ['required','string','max:20','regex:/^[A-Z0-9_-]+$/']
        ];
    }
}
