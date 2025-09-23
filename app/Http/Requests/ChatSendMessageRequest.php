<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ChatSendMessageRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()!=null; }
    public function rules(): array
    {
        return [
            'conversation_id'=>['required','integer','exists:chat_conversations,id'],
            'type'=>['required','in:text,file,image,audio'],
            'body'=>['nullable','string','max:5000'],
            'attachment'=>['nullable','file','max:10240','required_unless:type,text'],
            'template_id'=>['nullable','integer','exists:chat_message_templates,id']
        ];
    }
    protected function prepareForValidation(): void
    {
        if($this->get('template_id')){ $this->merge(['type'=>'text']); }
    }
    public function after(): array
    {
        return [function(Validator $v){
            $template = $this->get('template_id');
            $body = trim((string)$this->get('body'));
            if(!$template && $this->get('type')==='text' && $body===''){
                $v->errors()->add('body','Mensagem de texto vazia ou template ausente.');
            }
        }];
    }
}
