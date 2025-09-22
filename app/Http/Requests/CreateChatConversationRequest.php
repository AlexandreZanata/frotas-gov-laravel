<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateChatConversationRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() != null; }
    public function rules(): array
    {
        return [
            'title'=>['nullable','string','max:150'],
            'is_group'=>['sometimes','boolean'],
            'participants'=>['nullable','array','max:50'],
            'participants.*'=>['integer','different:'.$this->user()->id],
        ];
    }
}

