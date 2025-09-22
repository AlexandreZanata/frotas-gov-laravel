<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TireExternalOutRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }
    public function rules(): array
    {
        return [
            'position' => ['required','in:FL,FR,RL,RR']
        ];
    }
}

