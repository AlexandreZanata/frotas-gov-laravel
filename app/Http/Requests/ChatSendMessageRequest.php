<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatSendMessageRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()!=null; }
    public function rules(): array
    {
        return [
            'conversation_id'=>['required','integer','exists:chat_conversations,id'],
            'type'=>['required','in:text,file,image,audio'],
            'body'=>['nullable','string','max:5000','required_if:type,text'],
            'attachment'=>['nullable','file','max:10240','required_unless:type,text'],
        ];
    }
}

