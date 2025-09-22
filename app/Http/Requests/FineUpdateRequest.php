<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FineUpdateRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        $id = $this->route('fine')?->id ?? null;
        return [
            'auto_number' => ['required','string','max:100','unique:fines,auto_number,'.($id ?? 'NULL').',id'],
            'vehicle_id' => ['required','exists:vehicles,id'],
            'driver_id' => ['nullable','exists:users,id'],
            'notes' => ['nullable','string','max:5000']
        ];
    }
}

