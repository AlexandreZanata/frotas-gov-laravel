<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TireInternalRotationRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }
    public function rules(): array
    {
        $positions = 'in:FL,FR,RL,RR';
        return [
            'pos_a' => ['required',$positions,'different:pos_b'],
            'pos_b' => ['required',$positions,'different:pos_a'],
        ];
    }
}

