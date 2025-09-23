<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TireReplacementRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }
    public function rules(): array
    {
        return [
            'replacements' => ['required','array','min:1'],
            'replacements.*' => ['required','integer','exists:tires,id']
        ];
    }
}

