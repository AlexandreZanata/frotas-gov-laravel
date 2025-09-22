<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FineStoreRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'auto_number' => ['required','string','max:100','unique:fines,auto_number'],
            'vehicle_id' => ['required','exists:vehicles,id'],
            'driver_id' => ['nullable','exists:users,id'],
            'notes' => ['nullable','string','max:5000']
        ];
    }
}

