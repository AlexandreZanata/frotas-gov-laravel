<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\{User, Role, Secretariat, ChatConversation, ChatMessageTemplate, ChatMessage};

uses(RefreshDatabase::class);

function createRole(int $id,string $name){ DB::table('roles')->updateOrInsert(['id'=>$id],['name'=>$name,'description'=>null]); }

beforeEach(function(){
    createRole(1,'Super Admin');
    createRole(2,'Secretariat Admin');
    createRole(3,'User');
    $this->s1 = Secretariat::factory()->create();
    $this->s2 = Secretariat::factory()->create();
    $this->admin1 = User::factory()->create(['role_id'=>1,'secretariat_id'=>$this->s1->id]);
    $this->admin2 = User::factory()->create(['role_id'=>2,'secretariat_id'=>$this->s1->id]);
    $this->userOther = User::factory()->create(['role_id'=>3,'secretariat_id'=>$this->s2->id]);
    $this->userS1 = User::factory()->create(['role_id'=>3,'secretariat_id'=>$this->s1->id]);
});

it('impede usuário comum de criar grupo', function(){
    $resp = $this->actingAs($this->userOther)->postJson(route('api.chat.conversations.store'),[
        'title'=>'Grupo X','is_group'=>true,'participants'=>[$this->admin1->id]
    ]);
    $resp->assertStatus(403)->assertJson(['message'=>'Apenas administradores podem criar grupos']);
});

it('permite super admin criar grupo', function(){
    $resp = $this->actingAs($this->admin1)->postJson(route('api.chat.conversations.store'),[
        'title'=>'Equipe','is_group'=>true,'participants'=>[$this->admin2->id,$this->userOther->id]
    ]);
    $resp->assertStatus(200)->assertJsonStructure(['conversation_id']);
});

it('permite admin de secretaria (role 2) criar grupo', function(){
    $resp = $this->actingAs($this->admin2)->postJson(route('api.chat.conversations.store'),[
        'title'=>'Time S1','is_group'=>true,'participants'=>[$this->admin1->id,$this->userS1->id]
    ]);
    $resp->assertStatus(200);
});

it('impede role 2 de criar template global', function(){
    $resp = $this->actingAs($this->admin2)->postJson(route('api.chat.templates.store'),[
        'title'=>'Aviso Global','body'=>'Corpo','scope'=>'global'
    ]);
    $resp->assertStatus(422); // regra custom devolve 422
});

it('permite role 2 criar template da secretaria', function(){
    $resp = $this->actingAs($this->admin2)->postJson(route('api.chat.templates.store'),[
        'title'=>'Aviso Local','body'=>'Mensagem secretaria','scope'=>'secretariat','style'=>['class'=>'bg-purple-600 text-white']
    ]);
    $resp->assertStatus(200)->assertJsonStructure(['id']);
    $this->assertDatabaseHas('chat_message_templates',['title'=>'Aviso Local']);
});

it('bloqueia role 2 de usar template para conversa com outra secretaria', function(){
    // cria template secretaria s1
    $tpl = ChatMessageTemplate::create([
        'title'=>'S1 Notice','body'=>'Olá S1','scope'=>'secretariat','style'=>['class'=>'bg-amber-600 text-white'],
        'secretariat_id'=>$this->s1->id,'created_by'=>$this->admin2->id
    ]);
    // cria grupo com user de outra secretaria (admin1 cria, inclui admin2 e userOther)
    $resp = $this->actingAs($this->admin1)->postJson(route('api.chat.conversations.store'),[
        'title'=>'InterSecretarias','is_group'=>true,'participants'=>[$this->admin2->id,$this->userOther->id]
    ]);
    $convId = $resp->json('conversation_id');
    // admin2 tenta enviar template
    $send = $this->actingAs($this->admin2)->postJson(route('api.chat.messages.send'),[
        'conversation_id'=>$convId,
        'type'=>'text',
        'template_id'=>$tpl->id
    ]);
    $send->assertStatus(403)->assertJson(['message'=>'Template só pode ser enviado para usuários da sua secretaria']);
});

it('permite role 2 usar template em conversa só da sua secretaria', function(){
    // template s1
    $tpl = ChatMessageTemplate::create([
        'title'=>'S1 OK','body'=>'Oi equipe','scope'=>'secretariat','style'=>['class'=>'bg-green-700 text-white'],
        'secretariat_id'=>$this->s1->id,'created_by'=>$this->admin2->id
    ]);
    // grupo s1
    $resp = $this->actingAs($this->admin2)->postJson(route('api.chat.conversations.store'),[
        'title'=>'Equipe Interna','is_group'=>true,'participants'=>[$this->admin1->id,$this->userS1->id]
    ]);
    $convId = $resp->json('conversation_id');
    $send = $this->actingAs($this->admin2)->postJson(route('api.chat.messages.send'),[
        'conversation_id'=>$convId,
        'type'=>'text',
        'template_id'=>$tpl->id
    ]);
    $send->assertStatus(200)->assertJsonStructure(['id']);
    $this->assertDatabaseHas('chat_messages',['conversation_id'=>$convId,'template_id'=>$tpl->id]);
});

it('permite qualquer usuário iniciar conversa direta', function(){
    $resp = $this->actingAs($this->userOther)->postJson(route('api.chat.direct',$this->admin1));
    $resp->assertStatus(200)->assertJsonStructure(['conversation_id']);
});

it('força mensagens de template a ter type text e corpo preenchido mesmo sem body explícito', function(){
    $tpl = ChatMessageTemplate::create([
        'title'=>'S1 Fixo','body'=>'Corpo Template','scope'=>'secretariat','style'=>['class'=>'bg-indigo-600 text-white'],
        'secretariat_id'=>$this->s1->id,'created_by'=>$this->admin2->id
    ]);
    // conversa direta admin2/admin1
    $direct = $this->actingAs($this->admin2)->postJson(route('api.chat.direct',$this->admin1));
    $convId = $direct->json('conversation_id');
    $send = $this->actingAs($this->admin2)->postJson(route('api.chat.messages.send'),[
        'conversation_id'=>$convId,
        'type'=>'text',
        'template_id'=>$tpl->id
    ]);
    $send->assertStatus(200);
    $msgId = $send->json('id');
    $this->assertDatabaseHas('chat_messages',['id'=>$msgId,'template_id'=>$tpl->id]);
    $this->assertEquals('Corpo Template', ChatMessage::find($msgId)->body);
});
