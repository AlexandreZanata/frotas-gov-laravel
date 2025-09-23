<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FineInfractionUpdateRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }
    public function rules(): array
    {
        return [
            'code' => ['required','string','max:50'],
            'description' => ['nullable','string','max:255'],
            'base_amount' => ['required','numeric','min:0'],
            'extra_fixed' => ['nullable','numeric','min:0'],
            'extra_percent' => ['nullable','numeric','min:0','max:100'],
            'discount_fixed' => ['nullable','numeric','min:0'],
            'discount_percent' => ['nullable','numeric','min:0','max:100'],
            'infraction_date' => ['nullable','date'],
            'due_date' => ['nullable','date','after_or_equal:infraction_date'],
            'notes' => ['nullable','string','max:1000']
        ];
    }
}

